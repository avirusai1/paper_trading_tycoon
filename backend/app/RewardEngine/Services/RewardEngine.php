<?php

declare(strict_types=1);

namespace App\RewardEngine\Services;

use App\RewardEngine\Actions\RollbackRewardAction;
use App\RewardEngine\Contracts\RewardContextBuilderContract;
use App\RewardEngine\Contracts\RewardEngineContract;
use App\RewardEngine\DTOs\RewardBatchResult;
use App\RewardEngine\DTOs\RewardEngineResult;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Enums\RewardType;
use App\RewardEngine\Exceptions\RewardEngineException;
use App\RewardEngine\Exceptions\RewardRollbackException;
use App\RewardEngine\Pipelines\RewardPipeline;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Central Reward Engine service — the single public entry point.
 *
 * Responsibilities:
 * 1. Build a RewardContext via the factory.
 * 2. Delegate to RewardPipeline for a single request.
 * 3. Aggregate multiple requests into a RewardBatchResult.
 * 4. Delegate rollbacks to RollbackRewardAction.
 *
 * This service has ZERO knowledge of:
 * - HTTP, controllers, API, or Flutter
 * - Trading, portfolio, or stock APIs
 * - Authentication or session state
 *
 * It may be called from:
 * - Queued event listeners (App\Listeners\*)
 * - Artisan commands (season close, mission refresh)
 * - Game Engine pipeline (RewardProcessor)
 * - Integration tests
 *
 * Registered as a singleton in AppServiceProvider.
 */
final class RewardEngine implements RewardEngineContract
{
    public function __construct(
        private readonly RewardContextBuilderContract $contextBuilder,
        private readonly RewardPipeline $pipeline,
        private readonly RollbackRewardAction $rollbackAction,
    ) {}

    /**
     * @throws RewardEngineException
     */
    public function distribute(RewardRequest $request): RewardEngineResult
    {
        $start = microtime(true);

        Log::info('[RewardEngine] distribute() called', [
            'user_id' => $request->userId,
            'reward_type' => $request->rewardType->value,
            'source' => $request->source->value,
            'key' => $request->idempotencyKey,
            'dry_run' => $request->dryRun,
        ]);

        try {
            $context = $this->contextBuilder->build($request);
            $result = $this->pipeline->execute($request, $context);

            $ms = round((microtime(true) - $start) * 1000, 2);

            Log::info('[RewardEngine] distribute() complete', [
                'user_id' => $request->userId,
                'status' => $result->status->value,
                'xp' => $result->totalXPGranted,
                'coins' => $result->totalCoinsGranted,
                'total_ms' => $ms,
            ]);

            return $result;
        } catch (RewardEngineException $e) {
            Log::error('[RewardEngine] distribute() failed', [
                'user_id' => $request->userId,
                'code' => $e->errorCode(),
                'message' => $e->getMessage(),
            ]);

            throw $e;
        } catch (Throwable $e) {
            Log::error('[RewardEngine] Unexpected error', [
                'user_id' => $request->userId,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            throw new RewardEngineException(
                "Unexpected error distributing reward: {$e->getMessage()}",
                'reward_engine_unexpected_error',
                500,
                $e,
            );
        }
    }

    /**
     * @param  RewardRequest[]  $requests
     */
    public function distributeBatch(array $requests): RewardBatchResult
    {
        $start = microtime(true);
        $results = [];

        foreach ($requests as $request) {
            try {
                $results[] = $this->distribute($request);
            } catch (Throwable $e) {
                // In batch mode, individual failures do not abort the batch.
                // Return a Failed result for this request and continue.
                Log::warning('[RewardEngine] Batch item failed', [
                    'user_id' => $request->userId,
                    'key' => $request->idempotencyKey,
                    'exception' => $e->getMessage(),
                ]);

                $results[] = new RewardEngineResult(
                    idempotencyKey: $request->idempotencyKey,
                    userId: $request->userId,
                    status: RewardStatus::Failed,
                    rewardType: $request->rewardType,
                    totalXPGranted: 0,
                    totalCoinsGranted: 0,
                    distributionResults: [],
                    failureReason: $e->getMessage(),
                );
            }
        }

        $ms = round((microtime(true) - $start) * 1000, 2);

        $userId = $requests[0]->userId ?? 0;

        Log::info('[RewardEngine] Batch complete', [
            'user_id' => $userId,
            'total' => count($results),
            'total_ms' => $ms,
        ]);

        return new RewardBatchResult($results, $userId, $ms);
    }

    /**
     * @throws RewardRollbackException
     */
    public function rollback(string $idempotencyKey, int $userId): RewardEngineResult
    {
        Log::info('[RewardEngine] rollback() called', [
            'user_id' => $userId,
            'idempotency_key' => $idempotencyKey,
        ]);

        // Build a synthetic rollback request to get a context
        $rollbackRequest = new RewardRequest(
            userId: $userId,
            rewardType: RewardType::AdminReward,
            source: RewardSource::Admin,
            sourceId: $idempotencyKey,
            idempotencyKey: $idempotencyKey.':rollback',
        );

        $context = $this->contextBuilder->build($rollbackRequest);

        $distributionResult = $this->rollbackAction->execute($idempotencyKey, $context);

        return new RewardEngineResult(
            idempotencyKey: $idempotencyKey,
            userId: $userId,
            status: RewardStatus::RolledBack,
            rewardType: $distributionResult->rewardType,
            totalXPGranted: 0,
            totalCoinsGranted: 0,
            distributionResults: [$distributionResult],
        );
    }
}
