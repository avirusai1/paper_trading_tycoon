<?php
declare(strict_types=1);

namespace App\RewardEngine\Actions;

use App\Models\RewardHistory;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardStrategyRegistryContract;
use App\RewardEngine\DTOs\DistributionResult;
use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Enums\RewardType;
use App\RewardEngine\Exceptions\RewardRollbackException;
use Illuminate\Support\Facades\Log;

/**
 * Action: roll back a previously distributed reward.
 *
 * Looks up the original reward history record to determine the RewardType,
 * selects the appropriate strategy, and calls strategy::rollback().
 *
 * Supports partial rollback detection: if the strategy's rollback returns
 * a non-RolledBack status, a RewardRollbackException is thrown.
 */
final class RollbackRewardAction
{
    public function __construct(
        private readonly RewardStrategyRegistryContract $registry,
    ) {}

    /**
     * @throws RewardRollbackException  If rollback fails or is only partial.
     */
    public function execute(string $idempotencyKey, RewardContext $context): DistributionResult
    {
        Log::info('[RewardEngine:RollbackAction] Rolling back reward', [
            'user_id'         => $context->userId(),
            'idempotency_key' => $idempotencyKey,
        ]);

        // Find the original reward history record to determine type
        $history = RewardHistory::query()
            ->where('user_id', $context->userId())
            ->where('source_id', $idempotencyKey)
            ->first();

        if ($history === null) {
            Log::warning('[RewardEngine:RollbackAction] No reward history found for rollback', [
                'user_id'         => $context->userId(),
                'idempotency_key' => $idempotencyKey,
            ]);

            // Nothing to roll back — treat as success
            return new DistributionResult(
                rewardType:     RewardType::AdminReward,
                status:         RewardStatus::RolledBack,
                idempotencyKey: $idempotencyKey,
                userId:         $context->userId(),
                wasIdempotent:  true,
            );
        }

        $rewardType = RewardType::tryFrom($history->source_type);

        if ($rewardType === null || ! $this->registry->has($rewardType)) {
            throw RewardRollbackException::partialRollback(
                $context->userId(),
                $idempotencyKey,
                "Unknown reward type '{$history->source_type}' — cannot roll back.",
            );
        }

        $strategyResult = $this->registry->get($rewardType)->rollback($idempotencyKey, $context);

        if (! $strategyResult->rolledBack && $strategyResult->status !== RewardStatus::RolledBack) {
            throw RewardRollbackException::partialRollback(
                $context->userId(),
                $idempotencyKey,
                "Strategy returned non-rollback status: {$strategyResult->status->value}",
            );
        }

        return new DistributionResult(
            rewardType:     $strategyResult->rewardType,
            status:         RewardStatus::RolledBack,
            idempotencyKey: $idempotencyKey,
            userId:         $context->userId(),
        );
    }
}
