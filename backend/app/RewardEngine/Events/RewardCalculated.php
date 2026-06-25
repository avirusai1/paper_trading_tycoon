<?php
declare(strict_types=1);

namespace App\RewardEngine\Events;

use App\RewardEngine\DTOs\CalculatedReward;

/**
 * Fired after calculation completes, before distribution begins.
 *
 * Downstream: preview cache invalidation; analytics pre-distribution tracking.
 */
final class RewardCalculated extends RewardEngineEvent
{
    public function __construct(
        public readonly int             $userId,
        public readonly CalculatedReward $calculatedReward,
    ) {
        parent::__construct();
    }
}
