<?php
declare(strict_types=1);

namespace App\RewardEngine\Events;

use App\RewardEngine\DTOs\RewardEngineResult;
use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardType;

/**
 * Fired after all distributors complete successfully and reward_history is written.
 *
 * Downstream: notifications, analytics, anti-cheat audit, leaderboard update.
 */
final class RewardGranted extends RewardEngineEvent
{
    public function __construct(
        public readonly int              $userId,
        public readonly RewardType       $rewardType,
        public readonly RewardSource     $source,
        public readonly string           $idempotencyKey,
        public readonly int              $xpGranted,
        public readonly int              $coinsGranted,
        public readonly RewardEngineResult $result,
    ) {
        parent::__construct();
    }
}
