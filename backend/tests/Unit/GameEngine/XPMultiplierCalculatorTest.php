<?php

declare(strict_types=1);

namespace Tests\Unit\GameEngine;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\Contracts\GameRuleProviderContract;
use App\GameEngine\Enums\PlayerState;
use App\GameEngine\Support\XPMultiplierCalculator;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\Wallet;
use Mockery;
use Tests\TestCase;

/**
 * Unit tests for XPMultiplierCalculator.
 *
 * Coverage targets:
 * - Base multiplier is 1.0 for non-premium user with no streak
 * - Premium users receive the premium multiplier from rules
 * - Login streaks apply correct tier multipliers (3, 7, 30 days)
 * - Equipped item boost is stacked multiplicatively
 * - Final result is capped at max_multiplier rule value
 */
final class XPMultiplierCalculatorTest extends TestCase
{
    private GameRuleProviderContract $rules;
    private XPMultiplierCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rules = Mockery::mock(GameRuleProviderContract::class);
        $this->calculator = new XPMultiplierCalculator($this->rules);

        // Default rules for tests that don't override them
        $this->rules->shouldReceive('getFloat')->with('xp.max_multiplier', 3.0)->andReturn(3.0)->byDefault();
        $this->rules->shouldReceive('getFloat')->with('xp.premium_multiplier', 1.5)->andReturn(1.5)->byDefault();
        $this->rules->shouldReceive('getFloat')->with('xp.streak_multiplier_30', 1.3)->andReturn(1.3)->byDefault();
        $this->rules->shouldReceive('getFloat')->with('xp.streak_multiplier_7', 1.2)->andReturn(1.2)->byDefault();
        $this->rules->shouldReceive('getFloat')->with('xp.streak_multiplier_3', 1.1)->andReturn(1.1)->byDefault();
    }

    /** @test */
    public function base_multiplier_is_one_for_non_premium_no_streak(): void
    {
        $context = $this->makeContext(isPremium: false, streak: 0);

        $result = $this->calculator->calculate($context);

        $this->assertEqualsWithDelta(1.0, $result, 0.001);
    }

    /** @test */
    public function premium_applies_premium_multiplier(): void
    {
        $context = $this->makeContext(isPremium: true, streak: 0);

        $result = $this->calculator->calculate($context);

        $this->assertEqualsWithDelta(1.5, $result, 0.001);
    }

    /** @test */
    public function streak_3_days_applies_tier_one_bonus(): void
    {
        $context = $this->makeContext(isPremium: false, streak: 3);

        $result = $this->calculator->calculate($context);

        $this->assertEqualsWithDelta(1.1, $result, 0.001);
    }

    /** @test */
    public function streak_7_days_applies_tier_two_bonus(): void
    {
        $context = $this->makeContext(isPremium: false, streak: 7);

        $result = $this->calculator->calculate($context);

        $this->assertEqualsWithDelta(1.2, $result, 0.001);
    }

    /** @test */
    public function streak_30_days_applies_tier_three_bonus(): void
    {
        $context = $this->makeContext(isPremium: false, streak: 30);

        $result = $this->calculator->calculate($context);

        $this->assertEqualsWithDelta(1.3, $result, 0.001);
    }

    /** @test */
    public function multiplier_is_capped_at_max(): void
    {
        // Premium (×1.5) × streak30 (×1.3) = 1.95 — under cap of 3.0
        // Force a larger item boost to exceed cap
        $context = $this->makeContext(isPremium: true, streak: 30, itemXpBoost: 3.0);

        // 1.5 × 1.3 × 3.0 = 5.85, should be capped at 3.0
        $result = $this->calculator->calculate($context);

        $this->assertEqualsWithDelta(3.0, $result, 0.001);
    }

    /** @test */
    public function premium_and_streak_stack_multiplicatively(): void
    {
        $context = $this->makeContext(isPremium: true, streak: 7);

        // 1.5 × 1.2 = 1.8
        $result = $this->calculator->calculate($context);

        $this->assertEqualsWithDelta(1.8, $result, 0.001);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeContext(
        bool $isPremium,
        int $streak,
        float $itemXpBoost = 1.0,
    ): GameContext {
        $user = new User(['name' => 'Test', 'email' => 'test@example.com']);
        $user->is_premium = $isPremium;
        $user->status = 'active';
        $user->id = 1;

        $wallet = new Wallet;
        $wallet->coin_balance = 0;

        $userLevel = new UserLevel;
        $userLevel->current_level = 1;
        $userLevel->current_xp = 0;

        return new GameContext(
            user: $user,
            playerState: $isPremium ? PlayerState::ActivePremium : PlayerState::Active,
            wallet: $wallet,
            userLevel: $userLevel,
            currentLeague: null,
            league: null,
            activeSeason: null,
            activeMissions: [],
            unlockedAchievementIds: [],
            loginStreakDays: $streak,
            activeMultipliers: ['xp' => $itemXpBoost, 'coins' => 1.0],
            featureFlags: [],
            builtAt: microtime(true),
        );
    }
}
