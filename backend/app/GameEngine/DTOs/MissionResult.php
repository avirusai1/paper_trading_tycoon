<?php
declare(strict_types=1);

namespace App\GameEngine\DTOs;

/**
 * Immutable result of advancing or completing a single mission.
 */
final readonly class MissionResult
{
    public function __construct(
        public readonly int    $userMissionId,
        public readonly int    $missionId,
        public readonly int    $userId,
        public readonly string $missionKey,
        public readonly int    $progressBefore,
        public readonly int    $progressAfter,
        public readonly int    $target,
        /** True if progressAfter >= target AND the mission was not already complete before. */
        public readonly bool   $justCompleted,
        /** True if the reward for a completed mission was also claimed in this operation. */
        public readonly bool   $rewardClaimed,
        public readonly int    $xpReward,
        public readonly int    $coinReward,
    ) {}

    public function progressPercent(): float
    {
        return $this->target === 0
            ? 100.0
            : min(100.0, ($this->progressAfter / $this->target) * 100);
    }
}
