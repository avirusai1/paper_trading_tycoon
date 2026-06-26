<?php

declare(strict_types=1);

namespace App\RewardEngine\DTOs;

use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Enums\RewardType;

/**
 * Final immutable result of a single reward pipeline run.
 *
 * Returned to the caller of RewardEngineContract::distribute().
 * Contains all collected distributor results, totals, and a snapshot
 * of what was granted.
 */
final readonly class RewardEngineResult
{
    /**
     * @param  DistributionResult[]  $distributionResults
     */
    public function __construct(
        public readonly string $idempotencyKey,
        public readonly int $userId,
        public readonly RewardStatus $status,
        public readonly RewardType $rewardType,
        public readonly int $totalXPGranted,
        public readonly int $totalCoinsGranted,
        public readonly array $distributionResults,
        public readonly array $extras = [],
        public readonly bool $wasIdempotent = false,
        public readonly ?string $failureReason = null,
        public readonly float $processingTimeMs = 0.0,
    ) {}

    public function succeeded(): bool
    {
        return $this->status === RewardStatus::Recorded
            || $this->status === RewardStatus::Skipped;
    }

    public function failed(): bool
    {
        return $this->status === RewardStatus::Failed;
    }

    public function wasRolledBack(): bool
    {
        return $this->status === RewardStatus::RolledBack;
    }
}
