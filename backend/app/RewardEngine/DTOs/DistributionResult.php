<?php

declare(strict_types=1);

namespace App\RewardEngine\DTOs;

use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Enums\RewardType;

/**
 * Immutable result of a single distributor's work.
 *
 * Created by XPDistributor, CoinsDistributor, InventoryDistributor, etc.
 * Collected by the pipeline into a FullRewardResult.
 */
final readonly class DistributionResult
{
    /**
     * @param  RewardType  $rewardType  The type that was distributed.
     * @param  RewardStatus  $status  Terminal status.
     * @param  string  $idempotencyKey  Forwarded from the request.
     * @param  int  $userId  Target player.
     * @param  int  $xpGranted  0 if this distributor didn't touch XP.
     * @param  int  $coinsGranted  Paise. 0 if this distributor didn't touch coins.
     * @param  int  $xpBefore  User's XP before distribution.
     * @param  int  $xpAfter  User's XP after distribution.
     * @param  int  $coinsBefore  User's coin balance (paise) before distribution.
     * @param  int  $coinsAfter  User's coin balance (paise) after distribution.
     * @param  array<string,mixed>  $extras  Distributor-specific payload (inventory record IDs, etc.)
     * @param  bool  $wasIdempotent  True if this was a duplicate that was silently no-op'd.
     * @param  string|null  $failureReason  Set when status = Failed.
     */
    public function __construct(
        public readonly RewardType $rewardType,
        public readonly RewardStatus $status,
        public readonly string $idempotencyKey,
        public readonly int $userId,
        public readonly int $xpGranted = 0,
        public readonly int $coinsGranted = 0,
        public readonly int $xpBefore = 0,
        public readonly int $xpAfter = 0,
        public readonly int $coinsBefore = 0,
        public readonly int $coinsAfter = 0,
        public readonly array $extras = [],
        public readonly bool $wasIdempotent = false,
        public readonly ?string $failureReason = null,
    ) {}

    public function succeeded(): bool
    {
        return $this->status === RewardStatus::Distributed
            || $this->status === RewardStatus::Recorded
            || $this->status === RewardStatus::Skipped;
    }

    public static function skipped(RewardType $type, string $idempotencyKey, int $userId): self
    {
        return new self(
            rewardType: $type,
            status: RewardStatus::Skipped,
            idempotencyKey: $idempotencyKey,
            userId: $userId,
            wasIdempotent: true,
        );
    }
}
