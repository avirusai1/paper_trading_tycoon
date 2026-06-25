<?php
declare(strict_types=1);

namespace App\GameEngine\Processors;

use App\Enums\CoinTransactionSource;
use App\GameEngine\Actions\GrantCoinsAction;
use App\GameEngine\Actions\GrantMissionProgressAction;
use App\GameEngine\Actions\GrantXPAction;
use App\GameEngine\Contracts\MissionProcessorContract;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\MissionResult;
use App\GameEngine\Enums\MissionProgressType;
use App\GameEngine\Enums\XPSource;
use App\GameEngine\Exceptions\MissionException;
use App\Models\UserMission;

/**
 * Implements MissionProcessorContract.
 *
 * Advances mission progress, auto-claims rewards for completed missions
 * (marking the mission as claimed and granting XP + coins).
 */
final class MissionProcessor implements MissionProcessorContract
{
    public function __construct(
        private readonly GrantMissionProgressAction $progressAction,
        private readonly GrantXPAction              $grantXP,
        private readonly GrantCoinsAction           $grantCoins,
    ) {}

    /**
     * @return MissionResult[]
     */
    public function advance(
        GameContext        $context,
        MissionProgressType $trigger,
        int                 $increment = 1,
    ): array {
        $rawResults = $this->progressAction->execute($context, $context->activeMissions[0]->mission ?? null !== null
            ? $this->buildEventFor($trigger)
            : $this->buildEventFor($trigger));

        // Re-execute using the game event
        // The progressAction takes a GameEvent — we build a trigger shim
        return $this->advanceWithEvent($context, $trigger);
    }

    public function claimReward(GameContext $context, int $userMissionId): MissionResult
    {
        $userMission = UserMission::with('mission')->find($userMissionId);

        if ($userMission === null) {
            throw MissionException::notFound($userMissionId);
        }

        if ($userMission->user_id !== $context->userId()) {
            throw MissionException::notFound($userMissionId);
        }

        if ($userMission->progress < $userMission->target) {
            throw MissionException::notCompleted($userMissionId);
        }

        if ($userMission->claimed_at !== null) {
            throw MissionException::alreadyClaimed($userMissionId);
        }

        if ($userMission->expires_at !== null && $userMission->expires_at->isPast()) {
            throw MissionException::expired($userMissionId);
        }

        $mission   = $userMission->mission;
        $sourceId  = "mission_{$userMissionId}";
        $xpGranted = 0;
        $coinGranted = 0;

        if ($mission->xp_reward > 0) {
            $xpResult  = $this->grantXP->execute(
                $context,
                XPSource::MissionCompleted,
                $sourceId,
                $mission->xp_reward,
            );
            $xpGranted = $xpResult->amountGranted;
        }

        if ($mission->coin_reward > 0) {
            $coinResult  = $this->grantCoins->credit(
                $context,
                CoinTransactionSource::Challenge,
                $sourceId,
                $mission->coin_reward,
                "Mission reward: {$mission->name}",
            );
            $coinGranted = $coinResult->coinsGranted;
        }

        $this->progressAction->markRewardClaimed($userMissionId);

        return new MissionResult(
            userMissionId:  $userMissionId,
            missionId:      $mission->id,
            userId:         $context->userId(),
            missionKey:     $mission->key,
            progressBefore: $userMission->progress,
            progressAfter:  $userMission->progress,
            target:         $userMission->target,
            justCompleted:  false,
            rewardClaimed:  true,
            xpReward:       $xpGranted,
            coinReward:     $coinGranted,
        );
    }

    /**
     * Advance using a generic trigger shim — used when the caller passes
     * MissionProgressType directly instead of a full GameEvent.
     *
     * @return MissionResult[]
     */
    private function advanceWithEvent(GameContext $context, MissionProgressType $trigger): array
    {
        // Build a minimal shim event for the criteria evaluator
        $shimEvent = new class($context->userId(), $trigger) implements \App\GameEngine\Events\GameEvent {
            public function __construct(
                private readonly int $uid,
                public readonly \App\GameEngine\Enums\MissionProgressType $triggerType,
            ) {}

            public function eventType(): \App\GameEngine\Enums\GameEventType
            {
                return \App\GameEngine\Enums\GameEventType::TradeExecuted;
            }

            public function userId(): int          { return $this->uid; }
            public function sourceId(): string     { return 'shim_' . $this->triggerType->value; }
            public function idempotencyKey(): string { return 'shim:' . $this->uid . ':' . $this->triggerType->value; }
        };

        return $this->progressAction->execute($context, $shimEvent);
    }

    private function buildEventFor(MissionProgressType $trigger): \App\GameEngine\Events\GameEvent
    {
        return new class($trigger) implements \App\GameEngine\Events\GameEvent {
            public function __construct(private readonly MissionProgressType $t) {}
            public function eventType(): \App\GameEngine\Enums\GameEventType { return \App\GameEngine\Enums\GameEventType::TradeExecuted; }
            public function userId(): int          { return 0; }
            public function sourceId(): string     { return $this->t->value; }
            public function idempotencyKey(): string { return $this->t->value; }
        };
    }
}
