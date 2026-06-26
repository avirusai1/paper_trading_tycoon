<?php

declare(strict_types=1);

namespace App\RewardEngine\Contracts;

use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\DTOs\StrategyResult;
use App\RewardEngine\Enums\RewardType;
use App\RewardEngine\Exceptions\RewardCalculationException;
use App\RewardEngine\Exceptions\RewardDistributionException;
use App\RewardEngine\Exceptions\RewardRollbackException;

/**
 * Strategy contract for distributing a single reward type.
 *
 * One implementation per RewardType (Strategy Pattern). New reward types are
 * added by implementing this interface and registering in RewardStrategyRegistry
 * — no modifications to the pipeline or distributor required (Open/Closed).
 *
 * Implementations must be idempotent: distributing the same request twice
 * must produce the same outcome without double-granting.
 */
interface RewardStrategyContract
{
    /**
     * The reward type this strategy handles.
     */
    public function handles(): RewardType;

    /**
     * Calculate the final reward payload from the request and context.
     * No DB writes occur here — calculation only.
     *
     * @throws RewardCalculationException
     */
    public function calculate(RewardRequest $request, RewardContext $context): CalculatedReward;

    /**
     * Persist the calculated reward and return a typed result.
     * All DB writes occur here — idempotent via the request's idempotency key.
     *
     * @throws RewardDistributionException
     */
    public function distribute(CalculatedReward $reward, RewardContext $context): StrategyResult;

    /**
     * Reverse a previously distributed reward.
     * Issues a compensating transaction for ledger types; deletes records for
     * non-ledger types (inventory items, badges).
     *
     * @throws RewardRollbackException
     */
    public function rollback(string $idempotencyKey, RewardContext $context): StrategyResult;
}
