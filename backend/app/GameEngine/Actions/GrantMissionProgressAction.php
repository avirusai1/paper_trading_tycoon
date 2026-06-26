<?php

declare(strict_types=1);

namespace App\GameEngine\Actions;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\MissionResult;
use App\GameEngine\Events\GameEvent;
use App\GameEngine\Exceptions\MissionException;
use App\GameEngine\Support\MissionCriteriaEvaluator;
use App\Models\UserMission;
use Illuminate\Support\Facades\DB;

/**
 * Advances progress on all active user missions that match the given event.
 *
 * Per mission:
 * 1. Evaluate criteria via MissionCriteriaEvaluator — get increment or 0.
 * 2. Increment progress atomically (lockForUpdate).
 * 3. Mark as completed if progress >= target.
 *
 * Reward grant (XP + coins) happens separately in the MissionProcessor
 * after this action returns, so rewards can be pipeline-dispatched.
 */
final class GrantMissionProgressAction
{
    public function __construct(
        private readonly MissionCriteriaEvaluator $criteriaEvaluator,
    ) {}

    /**
     * @return MissionResult[]
     */
    public function execute(GameContext $context, GameEvent $event): array
    {
        $results = [];

        foreach ($context->activeMissions as $userMission) {
            $increment = $this->criteriaEvaluator->incrementFor($userMission, $event);

            if ($increment <= 0) {
                continue;
            }

            $result = DB::transaction(function () use ($userMission, $increment): MissionResult {
                /** @var UserMission $locked */
                $locked = UserMission::lockForUpdate()->findOrFail($userMission->id);
                $progressBefore = $locked->progress;
                $progressAfter = min($locked->target, $progressBefore + $increment);
                $justCompleted = $progressAfter >= $locked->target && $progressBefore < $locked->target;

                $locked->update([
                    'progress' => $progressAfter,
                    'status' => $justCompleted ? 'completed' : $locked->status,
                    'completed_at' => $justCompleted ? now() : $locked->completed_at,
                ]);

                $mission = $locked->mission;

                return new MissionResult(
                    userMissionId: $locked->id,
                    missionId: $locked->mission_id,
                    userId: $locked->user_id,
                    missionKey: $mission->key,
                    progressBefore: $progressBefore,
                    progressAfter: $progressAfter,
                    target: $locked->target,
                    justCompleted: $justCompleted,
                    rewardClaimed: false,
                    xpReward: $mission->xp_reward,
                    coinReward: $mission->coin_reward,
                );
            });

            $results[] = $result;
        }

        return $results;
    }

    /**
     * Claim the XP and coin reward for a completed mission.
     * Called by the MissionProcessor after rewards have been granted.
     *
     * @throws MissionException
     */
    public function markRewardClaimed(int $userMissionId): void
    {
        $userMission = UserMission::find($userMissionId);

        if ($userMission === null) {
            throw MissionException::notFound($userMissionId);
        }

        if ($userMission->status !== 'completed') {
            throw MissionException::notCompleted($userMissionId);
        }

        if ($userMission->claimed_at !== null) {
            throw MissionException::alreadyClaimed($userMissionId);
        }

        $userMission->update([
            'status' => 'claimed',
            'claimed_at' => now(),
        ]);
    }
}
