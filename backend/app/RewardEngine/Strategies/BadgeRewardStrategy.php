<?php

declare(strict_types=1);

namespace App\RewardEngine\Strategies;

use App\Models\StoreItem;
use App\Models\UserInventory;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardStrategyContract;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\DTOs\StrategyResult;
use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Enums\RewardType;
use App\RewardEngine\Exceptions\RewardDistributionException;
use Illuminate\Support\Facades\Log;

/**
 * Strategy for granting badge-type store items as achievements rewards.
 *
 * Badges are store items with item_type = 'badge'.
 * Required metadata key: 'store_item_id' (int)
 *
 * Functionally identical to InventoryRewardStrategy but typed as RewardType::Badge
 * so the pipeline can distinguish badge grants from generic inventory grants.
 */
final class BadgeRewardStrategy implements RewardStrategyContract
{
    public function handles(): RewardType
    {
        return RewardType::Badge;
    }

    public function calculate(RewardRequest $request, RewardContext $context): CalculatedReward
    {
        return new CalculatedReward(
            rewardType: RewardType::Badge,
            idempotencyKey: $request->idempotencyKey,
            userId: $request->userId,
            extras: ['store_item_id' => (int) $request->meta('store_item_id', 0)],
            isDryRun: $request->dryRun,
        );
    }

    public function distribute(CalculatedReward $reward, RewardContext $context): StrategyResult
    {
        if ($reward->isDryRun) {
            return new StrategyResult(
                rewardType: $this->handles(),
                status: RewardStatus::Validated,
                idempotencyKey: $reward->idempotencyKey,
                userId: $reward->userId,
            );
        }

        $storeItemId = (int) ($reward->extras['store_item_id'] ?? 0);

        if ($storeItemId === 0) {
            throw RewardDistributionException::itemUnavailable(0);
        }

        $item = StoreItem::query()
            ->where('id', $storeItemId)
            ->where('item_type', 'badge')
            ->first();

        if ($item === null || ! $item->isAvailable()) {
            throw RewardDistributionException::itemUnavailable($storeItemId);
        }

        // Idempotency — check for existing grant with this reward key
        $exists = UserInventory::query()
            ->where('user_id', $reward->userId)
            ->where('store_item_id', $storeItemId)
            ->whereJsonContains('metadata->reward_key', $reward->idempotencyKey)
            ->exists();

        if ($exists) {
            return new StrategyResult(
                rewardType: $this->handles(),
                status: RewardStatus::Skipped,
                idempotencyKey: $reward->idempotencyKey,
                userId: $reward->userId,
                wasIdempotent: true,
            );
        }

        $inventory = UserInventory::create([
            'user_id' => $reward->userId,
            'store_item_id' => $storeItemId,
            'quantity' => 1,
            'is_equipped' => false,
            'metadata' => ['reward_key' => $reward->idempotencyKey, 'type' => 'badge'],
            'purchased_at' => now(),
        ]);

        Log::info('[RewardEngine:BadgeStrategy] Badge granted', [
            'user_id' => $reward->userId,
            'store_item_id' => $storeItemId,
        ]);

        return new StrategyResult(
            rewardType: $this->handles(),
            status: RewardStatus::Distributed,
            idempotencyKey: $reward->idempotencyKey,
            userId: $reward->userId,
            extras: ['inventory_id' => $inventory->id],
        );
    }

    public function rollback(string $idempotencyKey, RewardContext $context): StrategyResult
    {
        UserInventory::query()
            ->where('user_id', $context->userId())
            ->whereJsonContains('metadata->reward_key', $idempotencyKey)
            ->delete();

        return StrategyResult::rolledBack($this->handles(), $idempotencyKey, $context->userId());
    }
}
