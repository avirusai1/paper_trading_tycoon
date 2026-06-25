<?php
declare(strict_types=1);

namespace App\RewardEngine\Events;

use App\RewardEngine\Enums\RewardType;

/**
 * Fired after a reward has been successfully rolled back.
 *
 * Downstream: alert engineering (may indicate upstream bug), analytics.
 */
final class RewardRolledBack extends RewardEngineEvent
{
    public function __construct(
        public readonly int        $userId,
        public readonly RewardType $rewardType,
        public readonly string     $idempotencyKey,
        public readonly string     $reason,
    ) {
        parent::__construct();
    }
}
