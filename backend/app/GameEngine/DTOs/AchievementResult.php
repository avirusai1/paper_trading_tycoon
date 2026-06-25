<?php
declare(strict_types=1);

namespace App\GameEngine\DTOs;

use App\Enums\AchievementTier;

/**
 * Immutable result of unlocking or evaluating a single achievement.
 */
final readonly class AchievementResult
{
    public function __construct(
        public readonly int            $userId,
        public readonly int            $achievementId,
        public readonly string         $achievementKey,
        public readonly string         $achievementName,
        public readonly AchievementTier $tier,
        public readonly int            $unlockCount,
        public readonly bool           $justUnlocked,
        public readonly int            $xpReward,
        public readonly int            $coinReward,
    ) {}
}
