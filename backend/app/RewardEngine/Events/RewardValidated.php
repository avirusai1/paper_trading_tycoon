<?php

declare(strict_types=1);

namespace App\RewardEngine\Events;

use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardType;

/**
 * Fired after all validators pass, before calculation begins.
 *
 * Downstream: analytics rate tracking; no functional side effects required.
 */
final class RewardValidated extends RewardEngineEvent
{
    public function __construct(
        public readonly int $userId,
        public readonly RewardType $rewardType,
        public readonly RewardSource $source,
        public readonly string $idempotencyKey,
    ) {
        parent::__construct();
    }
}
