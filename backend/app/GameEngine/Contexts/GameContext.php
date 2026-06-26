<?php

declare(strict_types=1);

namespace App\GameEngine\Contexts;

use App\Enums\LeagueTier;
use App\GameEngine\DTOs\GameContextSnapshot;
use App\GameEngine\Enums\PlayerState;
use App\Models\League;
use App\Models\Season;
use App\Models\User;
use App\Models\UserLeague;
use App\Models\UserLevel;
use App\Models\UserMission;
use App\Models\Wallet;

/**
 * Immutable, fully-hydrated snapshot of a player's game state at a point in time.
 *
 * GameContext is the single source of truth for all game engine decision-making.
 * It is built by GameContextBuilder before the pipeline runs and is never mutated
 * by pipeline stages — stages persist changes to the DB and return DTOs.
 *
 * Property visibility is intentionally public readonly to allow processors to
 * read state without setter injection (immutability enforced by readonly).
 */
final readonly class GameContext
{
    /**
     * @param  UserMission[]  $activeMissions
     * @param  int[]  $unlockedAchievementIds
     * @param  array<string, bool>  $featureFlags
     * @param  array<string, mixed>  $activeMultipliers
     */
    public function __construct(
        // ── Identity ──────────────────────────────────────────────────────────
        public readonly User $user,
        public readonly PlayerState $playerState,

        // ── Economy ───────────────────────────────────────────────────────────
        public readonly Wallet $wallet,

        // ── XP & Level ────────────────────────────────────────────────────────
        public readonly UserLevel $userLevel,

        // ── League & Season ───────────────────────────────────────────────────
        public readonly ?UserLeague $currentLeague,
        public readonly ?League $league,
        public readonly ?Season $activeSeason,

        // ── Missions ──────────────────────────────────────────────────────────
        /** All UserMission records that are currently active (not expired, not claimed). */
        public readonly array $activeMissions,

        // ── Achievements ──────────────────────────────────────────────────────
        /** IDs of achievements the user has already unlocked (for skip checks). */
        public readonly array $unlockedAchievementIds,

        // ── Profile metadata ──────────────────────────────────────────────────
        public readonly int $loginStreakDays,

        // ── Multipliers ───────────────────────────────────────────────────────
        /**
         * Keyed multiplier map. Known keys: 'xp', 'coins'.
         * Values are floats; 1.0 = no bonus.
         * Sourced from equipped store items and premium status.
         */
        public readonly array $activeMultipliers,

        // ── Feature Flags ─────────────────────────────────────────────────────
        /** Map of feature flag key → bool for the current user. */
        public readonly array $featureFlags,

        /** Unix timestamp (microseconds) when this context was built. */
        public readonly float $builtAt,
    ) {}

    // ── Convenience accessors ─────────────────────────────────────────────────

    public function userId(): int
    {
        return $this->user->id;
    }

    public function isPremium(): bool
    {
        return $this->playerState->isPremium();
    }

    public function canParticipate(): bool
    {
        return $this->playerState->canParticipate();
    }

    public function currentLevel(): int
    {
        return $this->userLevel->current_level;
    }

    public function currentXP(): int
    {
        return $this->userLevel->current_xp;
    }

    public function coinBalance(): int
    {
        return $this->wallet->coin_balance;
    }

    public function xpMultiplier(): float
    {
        return (float) ($this->activeMultipliers['xp'] ?? 1.0);
    }

    public function coinMultiplier(): float
    {
        return (float) ($this->activeMultipliers['coins'] ?? 1.0);
    }

    public function hasFeature(string $flagKey): bool
    {
        return (bool) ($this->featureFlags[$flagKey] ?? false);
    }

    public function hasUnlockedAchievement(int $achievementId): bool
    {
        return in_array($achievementId, $this->unlockedAchievementIds, true);
    }

    /**
     * Produce a serializable snapshot (no Eloquent models).
     * Suitable for caching, queued jobs, and API responses.
     */
    public function toSnapshot(): GameContextSnapshot
    {
        return new GameContextSnapshot(
            userId: $this->user->id,
            userName: $this->user->name,
            playerState: $this->playerState,
            isPremium: $this->isPremium(),
            currentXP: $this->userLevel->current_xp,
            currentLevel: $this->userLevel->current_level,
            xpToNextLevel: $this->userLevel->xp_in_current_level,
            xpInCurrentLevel: $this->userLevel->xp_in_current_level,
            careerTitle: $this->userLevel->career_title,
            virtualCashPaise: $this->wallet->virtual_cash_paise,
            coinBalance: $this->wallet->coin_balance,
            leagueTier: $this->currentLeague?->tier !== null
                ? LeagueTier::from($this->currentLeague->tier)
                : null,
            leagueRank: $this->currentLeague?->rank_position,
            activeSeasonId: $this->activeSeason?->id,
            loginStreakDays: $this->loginStreakDays,
            xpMultiplier: $this->xpMultiplier(),
            builtAt: date('c', (int) $this->builtAt),
        );
    }
}
