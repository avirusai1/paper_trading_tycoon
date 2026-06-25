<?php
declare(strict_types=1);

namespace App\RewardEngine\Events;

use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardType;
use Throwable;

/**
 * Fired when a reward pipeline run fails at any stage.
 *
 * Downstream: alert engineering, increment failure counter, log to analytics.
 */
final class RewardFailed extends RewardEngineEvent
{
    public function __construct(
        public readonly int          $userId,
        public readonly RewardType   $rewardType,
        public readonly RewardSource $source,
        public readonly string       $idempotencyKey,
        public readonly string       $failureCode,
        public readonly string       $failureMessage,
        public readonly Throwable    $exception,
    ) {
        parent::__construct();
    }
}
