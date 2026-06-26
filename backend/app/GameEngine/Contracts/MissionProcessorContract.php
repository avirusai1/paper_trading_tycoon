<?php

declare(strict_types=1);

namespace App\GameEngine\Contracts;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\MissionResult;
use App\GameEngine\Enums\MissionProgressType;
use App\GameEngine\Exceptions\MissionException;

/**
 * Contract for the Mission (Challenge) processing subsystem.
 *
 * Responsibilities:
 * - Find all active missions for the user that match the current event type.
 * - Increment progress for each matching mission.
 * - Mark missions as completed when target is reached.
 * - Trigger reward dispatch via RewardProcessorContract when completed.
 *
 * Does NOT dispatch domain events — the calling pipeline handles that.
 */
interface MissionProcessorContract
{
    /**
     * Advance mission progress for all active missions matching the trigger.
     *
     * @return MissionResult[]
     *
     * @throws MissionException
     */
    public function advance(
        GameContext $context,
        MissionProgressType $trigger,
        int $increment = 1,
    ): array;

    /**
     * Claim the reward for a completed, unclaimed mission.
     *
     * @throws MissionException If mission not found, not completed, or already claimed.
     */
    public function claimReward(GameContext $context, int $userMissionId): MissionResult;
}
