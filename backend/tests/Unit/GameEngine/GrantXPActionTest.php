<?php
declare(strict_types=1);

namespace Tests\Unit\GameEngine;

use App\GameEngine\Actions\GrantXPAction;
use App\GameEngine\Contracts\GameRuleProviderContract;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\XPResult;
use App\GameEngine\Enums\XPSource;
use App\GameEngine\Support\DailyCapTracker;
use App\GameEngine\Support\XPMultiplierCalculator;
use App\Models\Level;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\Wallet;
use App\Models\XpLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * Unit tests for GrantXPAction.
 *
 * Coverage targets:
 * - Grants correct XP amount and writes XpLog
 * - Applies XP multiplier from context
 * - Enforces daily cap — reduces grant if cap already partially consumed
 * - Skips grant entirely when daily cap is exhausted
 * - Returns zero XPResult when source has no rule value (base = 0)
 * - Detects level-up and updates UserLevel.current_level
 * - Idempotent — duplicate source_id returns existing log without double-granting
 */
final class GrantXPActionTest extends TestCase
{
    use RefreshDatabase;

    private GameRuleProviderContract $rules;
    private XPMultiplierCalculator   $multiplierCalc;
    private DailyCapTracker          $capTracker;
    private GrantXPAction            $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rules          = Mockery::mock(GameRuleProviderContract::class);
        $this->multiplierCalc = Mockery::mock(XPMultiplierCalculator::class);
        $this->capTracker     = Mockery::mock(DailyCapTracker::class);

        $this->action = new GrantXPAction(
            $this->rules,
            $this->multiplierCalc,
            $this->capTracker,
        );

        // Seed the minimum level record so calculateLevel() doesn't fail
        Level::factory()->create(['level_number' => 1, 'xp_required' => 0,   'xp_to_next_level' => 100]);
        Level::factory()->create(['level_number' => 2, 'xp_required' => 100, 'xp_to_next_level' => 150]);
    }

    /** @test */
    public function it_grants_xp_and_writes_log(): void
    {
        $user      = User::factory()->create();
        $wallet    = Wallet::factory()->create(['user_id' => $user->id]);
        $userLevel = UserLevel::factory()->create(['user_id' => $user->id, 'current_xp' => 0, 'current_level' => 1]);
        $context   = $this->buildContext($user, $wallet, $userLevel);

        $this->rules->shouldReceive('getInt')->with('xp.trade_buy', 0)->andReturn(10);
        $this->multiplierCalc->shouldReceive('calculate')->andReturn(1.0);
        $this->capTracker->shouldReceive('getDailyTotal')->andReturn(0);
        $this->capTracker->shouldReceive('hasDailyCap')->passthru();
        $this->capTracker->shouldReceive('increment')->once();

        $result = $this->action->execute($context, XPSource::TradeBuy, 'trade_001');

        $this->assertInstanceOf(XPResult::class, $result);
        $this->assertSame(10, $result->amountGranted);
        $this->assertSame(0,  $result->xpBefore);
        $this->assertSame(10, $result->xpAfter);
        $this->assertFalse($result->didLevelUp);
        $this->assertDatabaseHas('xp_logs', [
            'user_id'   => $user->id,
            'amount'    => 10,
            'source'    => 'trade_buy',
            'source_id' => 'trade_001',
        ]);
    }

    /** @test */
    public function it_applies_multiplier(): void
    {
        $user      = User::factory()->create();
        $wallet    = Wallet::factory()->create(['user_id' => $user->id]);
        $userLevel = UserLevel::factory()->create(['user_id' => $user->id, 'current_xp' => 0, 'current_level' => 1]);
        $context   = $this->buildContext($user, $wallet, $userLevel);

        $this->rules->shouldReceive('getInt')->with('xp.trade_buy', 0)->andReturn(10);
        $this->multiplierCalc->shouldReceive('calculate')->andReturn(2.0);
        $this->capTracker->shouldReceive('getDailyTotal')->andReturn(0);
        $this->capTracker->shouldReceive('increment')->once();

        $result = $this->action->execute($context, XPSource::TradeBuy, 'trade_multiplier');

        $this->assertSame(20, $result->amountGranted); // 10 × 2.0
    }

    /** @test */
    public function it_detects_level_up(): void
    {
        $user      = User::factory()->create();
        $wallet    = Wallet::factory()->create(['user_id' => $user->id]);
        $userLevel = UserLevel::factory()->create(['user_id' => $user->id, 'current_xp' => 95, 'current_level' => 1]);
        $context   = $this->buildContext($user, $wallet, $userLevel);

        $this->rules->shouldReceive('getInt')->with('xp.trade_buy', 0)->andReturn(10);
        $this->multiplierCalc->shouldReceive('calculate')->andReturn(1.0);
        $this->capTracker->shouldReceive('getDailyTotal')->andReturn(0);
        $this->capTracker->shouldReceive('increment')->once();

        $result = $this->action->execute($context, XPSource::TradeBuy, 'trade_levelup');

        $this->assertTrue($result->didLevelUp);
        $this->assertSame(1, $result->levelBefore);
        $this->assertSame(2, $result->levelAfter);
    }

    /** @test */
    public function it_returns_zero_when_daily_cap_exhausted(): void
    {
        $user      = User::factory()->create();
        $wallet    = Wallet::factory()->create(['user_id' => $user->id]);
        $userLevel = UserLevel::factory()->create(['user_id' => $user->id, 'current_xp' => 0, 'current_level' => 1]);
        $context   = $this->buildContext($user, $wallet, $userLevel);

        $this->rules->shouldReceive('getInt')->with('xp.trade_buy', 0)->andReturn(10);
        $this->rules->shouldReceive('getInt')->with('xp.daily_cap.trade_buy')->andReturn(100);
        $this->multiplierCalc->shouldReceive('calculate')->andReturn(1.0);
        $this->capTracker->shouldReceive('getDailyTotal')->andReturn(100); // Already at cap

        $result = $this->action->execute($context, XPSource::TradeBuy, 'trade_capped');

        $this->assertSame(0, $result->amountGranted);
        $this->assertTrue($result->wasCapApplied);
        $this->assertDatabaseMissing('xp_logs', ['source_id' => 'trade_capped']);
    }

    /** @test */
    public function it_is_idempotent_on_duplicate_source_id(): void
    {
        $user      = User::factory()->create();
        $wallet    = Wallet::factory()->create(['user_id' => $user->id]);
        $userLevel = UserLevel::factory()->create(['user_id' => $user->id, 'current_xp' => 0, 'current_level' => 1]);
        $context   = $this->buildContext($user, $wallet, $userLevel);

        $this->rules->shouldReceive('getInt')->with('xp.trade_buy', 0)->andReturn(10);
        $this->multiplierCalc->shouldReceive('calculate')->andReturn(1.0);
        $this->capTracker->shouldReceive('getDailyTotal')->andReturn(0);
        $this->capTracker->shouldReceive('increment')->once(); // Only incremented on first grant

        // First grant
        $this->action->execute($context, XPSource::TradeBuy, 'idempotent_trade');

        // Second grant — same source_id
        $result = $this->action->execute($context, XPSource::TradeBuy, 'idempotent_trade');

        $this->assertSame(10, $result->amountGranted);
        $this->assertDatabaseCount('xp_logs', 1); // Only one log record
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildContext(User $user, Wallet $wallet, UserLevel $userLevel): GameContext
    {
        return new GameContext(
            user:                   $user,
            playerState:            \App\GameEngine\Enums\PlayerState::Active,
            wallet:                 $wallet,
            userLevel:              $userLevel,
            currentLeague:          null,
            league:                 null,
            activeSeason:           null,
            activeMissions:         [],
            unlockedAchievementIds: [],
            loginStreakDays:        0,
            activeMultipliers:      ['xp' => 1.0, 'coins' => 1.0],
            featureFlags:           [],
            builtAt:                microtime(true),
        );
    }
}
