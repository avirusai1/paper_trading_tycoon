<?php
declare(strict_types=1);

namespace App\RewardEngine\Contracts;

use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\DistributionResult;

/**
 * Contract for persisting a calculated reward to its target stores
 * (wallet, XP ledger, inventory, reward_history, etc.).
 *
 * Distributors are type-specific: XPDistributor writes to xp_logs,
 * CoinsDistributor writes to coin_transactions, etc. The pipeline
 * selects the correct distributor via the RewardType.
 *
 * All writes must be idempotent — duplicate idempotency keys silently no-op.
 */
interface RewardDistributorContract
{
    /**
     * Persist the calculated reward. Idempotent.
     *
     * @throws \App\RewardEngine\Exceptions\RewardDistributionException
     */
    public function distribute(CalculatedReward $reward, RewardContext $context): DistributionResult;

    /**
     * Reverse a previously distributed reward.
     *
     * @throws \App\RewardEngine\Exceptions\RewardRollbackException
     */
    public function rollback(string $idempotencyKey, RewardContext $context): DistributionResult;
}
