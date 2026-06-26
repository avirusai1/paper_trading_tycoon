<?php

declare(strict_types=1);

namespace Tests\Unit\GameEngine;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\GameContextSnapshot;
use App\GameEngine\Enums\PlayerState;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\Wallet;
use Tests\TestCase;

/**
 * Unit tests for GameContext.
 *
 * Coverage targets:
 * - canParticipate() returns true for Active and ActivePremium states
 * - canParticipate() returns false for Suspended and Banned states
 * - isPremium() returns correct value based on PlayerState
 * - xpMultiplier() and coinMultiplier() read from activeMultipliers
 * - hasFeature() returns correct bool for flag key lookup
 * - hasUnlockedAchievement() returns correct bool
 * - toSnapshot() returns a GameContextSnapshot with matching values
 */
final class GameContextTest extends TestCase
{
    /** @test */
    public function active_player_can_participate(): void
    {
        $context = $this->makeContext(PlayerState::Active);
        $this->assertTrue($context->canParticipate());
    }

    /** @test */
    public function active_premium_can_participate(): void
    {
        $context = $this->makeContext(PlayerState::ActivePremium);
        $this->assertTrue($context->canParticipate());
    }

    /** @test */
    public function suspended_player_cannot_participate(): void
    {
        $context = $this->makeContext(PlayerState::Suspended);
        $this->assertFalse($context->canParticipate());
    }

    /** @test */
    public function banned_player_cannot_participate(): void
    {
        $context = $this->makeContext(PlayerState::Banned);
        $this->assertFalse($context->canParticipate());
    }

    /** @test */
    public function is_premium_is_true_for_active_premium(): void
    {
        $context = $this->makeContext(PlayerState::ActivePremium);
        $this->assertTrue($context->isPremium());
    }

    /** @test */
    public function is_premium_is_false_for_active(): void
    {
        $context = $this->makeContext(PlayerState::Active);
        $this->assertFalse($context->isPremium());
    }

    /** @test */
    public function xp_multiplier_reads_from_active_multipliers(): void
    {
        $context = $this->makeContext(PlayerState::Active, multipliers: ['xp' => 2.5, 'coins' => 1.0]);
        $this->assertEqualsWithDelta(2.5, $context->xpMultiplier(), 0.001);
    }

    /** @test */
    public function coin_multiplier_defaults_to_one_when_not_set(): void
    {
        $context = $this->makeContext(PlayerState::Active, multipliers: []);
        $this->assertEqualsWithDelta(1.0, $context->coinMultiplier(), 0.001);
    }

    /** @test */
    public function has_feature_returns_true_for_enabled_flag(): void
    {
        $context = $this->makeContext(PlayerState::Active, flags: ['new_missions' => true]);
        $this->assertTrue($context->hasFeature('new_missions'));
    }

    /** @test */
    public function has_feature_returns_false_for_unknown_flag(): void
    {
        $context = $this->makeContext(PlayerState::Active, flags: []);
        $this->assertFalse($context->hasFeature('unknown_feature'));
    }

    /** @test */
    public function has_unlocked_achievement_returns_true_for_known_id(): void
    {
        $context = $this->makeContext(PlayerState::Active, unlockedIds: [1, 5, 12]);
        $this->assertTrue($context->hasUnlockedAchievement(5));
    }

    /** @test */
    public function has_unlocked_achievement_returns_false_for_unknown_id(): void
    {
        $context = $this->makeContext(PlayerState::Active, unlockedIds: [1, 5]);
        $this->assertFalse($context->hasUnlockedAchievement(99));
    }

    /** @test */
    public function to_snapshot_returns_game_context_snapshot(): void
    {
        $context = $this->makeContext(PlayerState::Active);
        $snapshot = $context->toSnapshot();

        $this->assertInstanceOf(GameContextSnapshot::class, $snapshot);
        $this->assertSame($context->userId(), $snapshot->userId);
        $this->assertSame($context->currentLevel(), $snapshot->currentLevel);
        $this->assertSame($context->currentXP(), $snapshot->currentXP);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeContext(
        PlayerState $state,
        array $multipliers = ['xp' => 1.0, 'coins' => 1.0],
        array $flags = [],
        array $unlockedIds = [],
    ): GameContext {
        $user = new User;
        $user->id = 1;
        $user->name = 'Test';
        $user->email = 'test@example.com';
        $user->is_premium = $state === PlayerState::ActivePremium;
        $user->status = 'active';

        $wallet = new Wallet;
        $wallet->coin_balance = 500;
        $wallet->virtual_cash_paise = 100_000_00;
        $wallet->total_deposited_paise = 100_000_00;

        $userLevel = new UserLevel;
        $userLevel->current_level = 5;
        $userLevel->current_xp = 400;
        $userLevel->xp_in_current_level = 50;
        $userLevel->career_title = 'Intern Trader';

        return new GameContext(
            user: $user,
            playerState: $state,
            wallet: $wallet,
            userLevel: $userLevel,
            currentLeague: null,
            league: null,
            activeSeason: null,
            activeMissions: [],
            unlockedAchievementIds: $unlockedIds,
            loginStreakDays: 0,
            activeMultipliers: $multipliers,
            featureFlags: $flags,
            builtAt: microtime(true),
        );
    }
}
