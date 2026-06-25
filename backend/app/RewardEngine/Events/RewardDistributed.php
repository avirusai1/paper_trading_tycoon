<?php
declare(strict_types=1);

namespace App\RewardEngine\Events;

use App\RewardEngine\DTOs\DistributionResult;
use App\RewardEngine\Enums\RewardType;

/**
 * Fired by each individual distributor after it persists its share of the reward.
 *
 * One event per distributor (XP, Coins, Inventory, Career) per pipeline run.
 * Downstream: push notification trigger, UI badge update.
 */
final class RewardDistributed extends RewardEngineEvent
{
    public function __construct(
        public readonly int                $userId,
        public readonly RewardType         $rewardType,
        public readonly DistributionResult $result,
    ) {
        parent::__construct();
    }
}
