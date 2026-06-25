<?php
declare(strict_types=1);

namespace App\RewardEngine\Distributors;

use App\Models\StoreItem;
use App\Models\UserInventory;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardDistributorContract;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\DistributionResult;
use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Exceptions\RewardDistributionException;
use Illuminate\Support\Facades\Log;

/**
 * Distributes inventory item rewards (free store item grants).
 *
 * Idempotent via metadata->reward_key JSON field on UserInventory.
 * Required extras key: 'store_item_id' (int).
 */
final class InventoryDistributor implements RewardDistributorContract
{
    public function distribute(CalculatedReward $reward, RewardContext $context): DistributionResult
    {
        if ($reward->isDryRun) {
            return new DistributionResult(
                rewardType:     $reward->rewardType,
                status:         RewardStatus::Skipped,
                idempotencyKey: $reward->idempotencyKey,
                userId:         $reward->userId,
            );
        }

        $storeItemId = (int) ($reward->extras['store_item_id'] ?? 0);

        if ($storeItemId === 0) {
            throw RewardDistributionException::itemUnavailable(0);
        }

        $item = StoreItem::query()->find($storeItemId);

        if ($item === null || ! $item->isAvailable()) {
            throw RewardDistributionException::itemUnavailable($storeItemId);
        }

        // Idempotency check
        $exists = UserInventory::query()
            ->where('user_id', $reward->userId)
            ->where('store_item_id', $storeItemId)
            ->whereJsonContains('metadata->reward_key', $reward->idempotencyKey)
            ->exists();

        if ($exists) {
            return DistributionResult::skipped($reward->rewardType, $reward->idempotencyKey, $reward->userId);
        }

        $inventory = UserInventory::create([
            'user_id'       => $reward->userId,
            'store_item_id' => $storeItemId,
            'quantity'      => 1,
            'is_equipped'   => false,
            'metadata'      => ['reward_key' => $reward->idempotencyKey],
            'expires_at'    => $reward->extras['expires_at'] ?? null,
            'purchased_at'  => now(),
        ]);

        Log::info('[RewardEngine:InventoryDistributor] Item granted', [
            'user_id'       => $reward->userId,
            'store_item_id' => $storeItemId,
            'inventory_id'  => $inventory->id,
        ]);

        return new DistributionResult(
            rewardType:     $reward->rewardType,
            status:         RewardStatus::Distributed,
            idempotencyKey: $reward->idempotencyKey,
            userId:         $reward->userId,
            extras:         ['inventory_id' => $inventory->id, 'store_item_id' => $storeItemId],
        );
    }

    public function rollback(string $idempotencyKey, RewardContext $context): DistributionResult
    {
        UserInventory::query()
            ->where('user_id', $context->userId())
            ->whereJsonContains('metadata->reward_key', $idempotencyKey)
            ->delete();

        return new DistributionResult(
            rewardType:     \App\RewardEngine\Enums\RewardType::InventoryItem,
            status:         RewardStatus::RolledBack,
            idempotencyKey: $idempotencyKey,
            userId:         $context->userId(),
        );
    }
}
