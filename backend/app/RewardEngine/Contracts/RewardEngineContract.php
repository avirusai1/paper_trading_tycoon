<?php
declare(strict_types=1);

namespace App\RewardEngine\Contracts;

use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\DTOs\RewardBatchResult;
use App\RewardEngine\DTOs\RewardEngineResult;

/**
 * Primary entry point for the Reward Engine subsystem.
 *
 * All reward distribution — regardless of source (mission, achievement,
 * level-up, admin grant, season, referral) — flows through this contract.
 * Implementations are responsible for running the full pipeline:
 * Validation → Calculation → Distribution → Ledger → Events.
 *
 * The Reward Engine has no knowledge of HTTP, controllers, or trading.
 */
interface RewardEngineContract
{
    /**
     * Process a single reward request through the full pipeline.
     *
     * @throws \App\RewardEngine\Exceptions\RewardValidationException   Validation failed.
     * @throws \App\RewardEngine\Exceptions\RewardCalculationException  Calculation failed.
     * @throws \App\RewardEngine\Exceptions\RewardDistributionException Distribution failed.
     * @throws \App\RewardEngine\Exceptions\RewardEngineException       Unexpected error.
     */
    public function distribute(RewardRequest $request): RewardEngineResult;

    /**
     * Process multiple reward requests as a logical unit.
     *
     * Individual failures do not abort the batch by default — each request
     * produces its own RewardEngineResult within the batch. Callers can
     * inspect RewardBatchResult::hasFailures() to detect partial success.
     *
     * @param  RewardRequest[]  $requests
     */
    public function distributeBatch(array $requests): RewardBatchResult;

    /**
     * Rollback a previously distributed reward by its idempotency key.
     *
     * Issues compensating transactions for ledger-backed reward types.
     * Non-ledger types (badges, titles) are revoked directly.
     *
     * @throws \App\RewardEngine\Exceptions\RewardRollbackException  If rollback fails partially.
     */
    public function rollback(string $idempotencyKey, int $userId): RewardEngineResult;
}
