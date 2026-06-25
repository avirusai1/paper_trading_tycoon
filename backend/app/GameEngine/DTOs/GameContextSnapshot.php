<?php
declare(strict_types=1);

namespace App\GameEngine\DTOs;

use App\GameEngine\Enums\PlayerState;
use App\Enums\LeagueTier;

/**
 * A serializable point-in-time snapshot of a player's full game state.
 *
 * Unlike GameContext (which holds live Eloquent models), GameContextSnapshot
 * is a pure value object suitable for caching, API serialization, and
 * passing to queued jobs.
 */
final readonly class GameContextSnapshot
{
    public function __construct(
        public readonly int         $userId,
        public readonly string      $userName,
        public readonly PlayerState $playerState,
        public readonly bool        $isPremium,

        // XP / Level
        public readonly int    $currentXP,
        public readonly int    $currentLevel,
        public readonly int    $xpToNextLevel,
        public readonly int    $xpInCurrentLevel,
        public readonly string $careerTitle,

        // Economy
        public readonly int    $virtualCashPaise,
        public readonly int    $coinBalance,

        // League
        public readonly ?LeagueTier $leagueTier,
        public readonly ?int        $leagueRank,
        public readonly ?int        $activeSeasonId,

        // Streak
        public readonly int $loginStreakDays,

        // XP multiplier (from premium, items, etc.) — float 1.0 = no bonus
        public readonly float $xpMultiplier,

        /** ISO-8601 timestamp of when the snapshot was built. */
        public readonly string $builtAt,
    ) {}
}
