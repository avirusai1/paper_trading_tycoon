<?php
declare(strict_types=1);

namespace Tests\Unit\GameEngine;

use App\GameEngine\Actions\GrantCareerProgressAction;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\CareerResult;
use App\GameEngine\Enums\PlayerState;
use App\GameEngine\Exceptions\CareerException;
use App\GameEngine\Processors\CareerProcessor;
use App\Models\CareerTitle;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for CareerProcessor + GrantCareerProgressAction.
 *
 * Coverage targets:
 * - Returns CareerResult with titleChanged = false when level is already in same title range
 * - Returns CareerResult with titleChanged = true when level crosses a title boundary
 * - Persists updated career_title to user_levels when changed
 * - Throws CareerException when no CareerTitle is defined for the level
 */
final class CareerProcessorTest extends TestCase
{
    use RefreshDatabase;

    private CareerProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new CareerProcessor(new GrantCareerProgressAction());

        // Seed career titles matching config/gamification.php
        CareerTitle::factory()->create(['min_level' => 1,  'max_level' => 5,  'title' => 'Student Trader']);
        CareerTitle::factory()->create(['min_level' => 6,  'max_level' => 10, 'title' => 'Intern Trader']);
        CareerTitle::factory()->create(['min_level' => 11, 'max_level' => 15, 'title' => 'Junior Trader']);
    }

    /** @test */
    public function title_unchanged_when_level_within_same_range(): void
    {
        $context = $this->makeContext(level: 3, title: 'Student Trader');

        $result = $this->processor->evaluate($context);

        $this->assertInstanceOf(CareerResult::class, $result);
        $this->assertFalse($result->titleChanged);
        $this->assertSame('Student Trader', $result->titleAfter);
    }

    /** @test */
    public function title_changes_when_level_crosses_boundary(): void
    {
        $context = $this->makeContext(level: 6, title: 'Student Trader');

        $result = $this->processor->evaluate($context);

        $this->assertTrue($result->titleChanged);
        $this->assertSame('Student Trader', $result->titleBefore);
        $this->assertSame('Intern Trader',  $result->titleAfter);
    }

    /** @test */
    public function it_persists_title_change_to_database(): void
    {
        $user      = User::factory()->create();
        $wallet    = Wallet::factory()->create(['user_id' => $user->id]);
        $userLevel = UserLevel::factory()->create([
            'user_id'       => $user->id,
            'current_level' => 11,
            'career_title'  => 'Intern Trader',
        ]);
        $context = $this->makeContextFromModels($user, $wallet, $userLevel);

        $this->processor->evaluate($context);

        $this->assertDatabaseHas('user_levels', [
            'user_id'      => $user->id,
            'career_title' => 'Junior Trader',
        ]);
    }

    /** @test */
    public function it_throws_career_exception_when_no_title_defined(): void
    {
        $this->expectException(CareerException::class);

        // Level 50 has no career title in our seeded data
        $context = $this->makeContext(level: 50, title: 'Unknown');
        $this->processor->evaluate($context);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeContext(int $level, string $title): GameContext
    {
        $user      = User::factory()->create();
        $wallet    = Wallet::factory()->create(['user_id' => $user->id]);
        $userLevel = UserLevel::factory()->create([
            'user_id'       => $user->id,
            'current_level' => $level,
            'career_title'  => $title,
        ]);
        return $this->makeContextFromModels($user, $wallet, $userLevel);
    }

    private function makeContextFromModels(User $user, Wallet $wallet, UserLevel $userLevel): GameContext
    {
        return new GameContext(
            user:                   $user,
            playerState:            PlayerState::Active,
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
