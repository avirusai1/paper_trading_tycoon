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
 * Strategy for granting store items as rewards (free inventory grants).
 *
 * Required metadata key: 'store_item_id' (int)
 * Optional metadata key: 'expires_at'    (string ISO 8601, null = permanent)
 *
 * Creates a UserInventory record with quantity=1, is_equipped=false, and
 * purchased_at=now(). Idempotent: if the user already has the item via this
 * idempotency key (checked via UserInventory.metadata['reward_key']), skips.
 */
final class InventoryRewardStrategy implements RewardStrategyContract
{
    public function handles(): RewardType
    {
        return RewardType::InventoryItem;
    }

    public function calculate(RewardRequest $request, RewardContext $context): CalculatedReward
    {
        // No coin/XP calculation needed — just pass through the request
        return new CalculatedReward(
            rewardType: RewardType::InventoryItem,
            idempotencyKey: $request->idempotencyKey,
            userId: $request->userId,
            extras: [
                'store_item_id' => (int) $request->meta('store_item_id', 0),
                'expires_at' => $request->meta('expires_at'),
            ],
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

        /** @var StoreItem|null $item */
        $item = StoreItem::query()->find($storeItemId);

        if ($item === null || ! $item->isAvailable()) {
            throw RewardDistributionException::itemUnavailable($storeItemId);
        }

        // Idempotency check via metadata JSON field
        $existing = UserInventory::query()
            ->where('user_id', $reward->userId)
            ->where('store_item_id', $storeItemId)
            ->whereJsonContains('metadata->reward_key', $reward->idempotencyKey)
            ->first();

        if ($existing !== null) {
            return new StrategyResult(
                rewardType: $this->handles(),
                status: RewardStatus::Skipped,
                idempotencyKey: $reward->idempotencyKey,
                userId: $reward->userId,
                wasIdempotent: true,
            );
        }

        /** @var UserInventory $inventory */
        $inventory = UserInventory::create([
            'user_id' => $reward->userId,
            'store_item_id' => $storeItemId,
            'quantity' => 1,
            'is_equipped' => false,
            'metadata' => ['reward_key' => $reward->idempotencyKey],
            'expires_at' => $reward->extras['expires_at'] ?? null,
            'purchased_at' => now(),
        ]);

        Log::info('[RewardEngine:InventoryStrategy] Item granted', [
            'user_id' => $reward->userId,
            'store_item_id' => $storeItemId,
            'inventory_id' => $inventory->id,
            'key' => $reward->idempotencyKey,
        ]);

        return new StrategyResult(
            rewardType: $this->handles(),
            status: RewardStatus::Distributed,
            idempotencyKey: $reward->idempotencyKey,
            userId: $reward->userId,
            extras: ['inventory_id' => $inventory->id, 'store_item_id' => $storeItemId],
        );
    }

    public function rollback(string $idempotencyKey, RewardContext $context): StrategyResult
    {
        // Delete inventory record granted by this reward key
        UserInventory::query()
            ->where('user_id', $context->userId())
            ->whereJsonContains('metadata->reward_key', $idempotencyKey)
            ->delete();

        Log::info('[RewardEngine:InventoryStrategy] Inventory reward rolled back', [
            'user_id' => $context->userId(),
            'idempotency_key' => $idempotencyKey,
        ]);

        return StrategyResult::rolledBack($this->handles(), $idempotencyKey, $context->userId());
    }
}
