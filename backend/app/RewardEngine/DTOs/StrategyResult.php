<?php

declare(strict_types=1);

namespace App\RewardEngine\DTOs;

use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Enums\RewardType;

/**
 * Immutable result returned by a RewardStrategy after distribution or rollback.
 *
 * Strategies return this; the pipeline wraps it into a FullRewardResult.
 */
final readonly class StrategyResult
{
    public function __construct(
        public readonly RewardType $rewardType,
        public readonly RewardStatus $status,
        public readonly string $idempotencyKey,
        public readonly int $userId,
        public readonly int $xpGranted = 0,
        public readonly int $coinsGranted = 0,
        public readonly array $extras = [],
        public readonly bool $rolledBack = false,
        public readonly ?string $failureReason = null,
    ) {}

    public function succeeded(): bool
    {
        return in_array($this->status, [
            RewardStatus::Distributed,
            RewardStatus::Recorded,
            RewardStatus::Skipped,
            RewardStatus::RolledBack,
        ], strict: true);
    }

    public static function rolledBack(RewardType $type, string $idempotencyKey, int $userId): self
    {
        return new self(
            rewardType: $type,
            status: RewardStatus::RolledBack,
            idempotencyKey: $idempotencyKey,
            userId: $userId,
            rolledBack: true,
        );
    }
}
