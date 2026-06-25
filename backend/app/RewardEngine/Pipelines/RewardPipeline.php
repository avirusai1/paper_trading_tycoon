<?php
declare(strict_types=1);

namespace App\RewardEngine\Pipelines;

use App\RewardEngine\Actions\DistributeRewardAction;
use App\RewardEngine\Actions\RecordRewardHistoryAction;
use App\RewardEngine\Actions\RollbackRewardAction;
use App\RewardEngine\Contracts\RewardValidatorContract;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\DistributionResult;
use App\RewardEngine\DTOs\RewardEngineResult;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Events\RewardCalculated;
use App\RewardEngine\Events\RewardDistributed;
use App\RewardEngine\Events\RewardFailed;
use App\RewardEngine\Events\RewardGranted;
use App\RewardEngine\Events\RewardRolledBack;
use App\RewardEngine\Events\RewardValidated;
use App\RewardEngine\Exceptions\RewardEngineException;
use App\RewardEngine\Exceptions\RewardValidationException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reward Engine pipeline — orchestrates the full reward lifecycle.
 *
 * Stage sequence:
 *   1. Validation chain (all validators)
 *   2. DistributeRewardAction::calculateOnly() → fire RewardCalculated
 *   3. DistributeRewardAction::execute() → fire RewardDistributed
 *   4. RecordRewardHistoryAction (write audit log)
 *   5. Fire RewardGranted
 *   6. Build and return RewardEngineResult
 *
 * Rollback on failure:
 *   - If distribution fails mid-way, RollbackRewardAction is called
 *     with the original idempotency key.
 *   - The pipeline catches all exceptions during distribution/recording,
 *     attempts rollback, then re-throws.
 *
 * The pipeline has NO knowledge of HTTP, controllers, or Flutter.
 */
final class RewardPipeline
{
    /**
     * @param  RewardValidatorContract[]  $validators  Executed in order.
     */
    public function __construct(
        private readonly array                   $validators,
        private readonly DistributeRewardAction  $distributeAction,
        private readonly RollbackRewardAction    $rollbackAction,
        private readonly RecordRewardHistoryAction $recordHistoryAction,
    ) {}

    /**
     * @throws RewardEngineException
     */
    public function execute(RewardRequest $request, RewardContext $context): RewardEngineResult
    {
        $startTime = microtime(true);

        // ── Stage 1: Validation chain ─────────────────────────────────────────
        try {
            foreach ($this->validators as $validator) {
                $validator->validate($request, $context);
            }

            Event::dispatch(new RewardValidated(
                $request->userId,
                $request->rewardType,
                $request->source,
                $request->idempotencyKey,
            ));
        } catch (RewardValidationException $e) {
            $elapsed = $this->elapsed($startTime);

            Log::info('[RewardPipeline] Validation failed', [
                'user_id' => $request->userId,
                'reason'  => $e->reason->value,
                'key'     => $request->idempotencyKey,
            ]);

            Event::dispatch(new RewardFailed(
                $request->userId,
                $request->rewardType,
                $request->source,
                $request->idempotencyKey,
                $e->reason->value,
                $e->getMessage(),
                $e,
            ));

            // Validation failures are not thrown — they return a Failed result.
            // This allows callers to inspect the failure reason without exception handling.
            return new RewardEngineResult(
                idempotencyKey:    $request->idempotencyKey,
                userId:            $request->userId,
                status:            RewardStatus::Failed,
                rewardType:        $request->rewardType,
                totalXPGranted:    0,
                totalCoinsGranted: 0,
                distributionResults: [],
                failureReason:     $e->reason->value,
                processingTimeMs:  $elapsed,
            );
        }

        // ── Stage 2: Calculate (no writes) ────────────────────────────────────
        try {
            $calculated = $this->distributeAction->calculateOnly($request, $context);

            Event::dispatch(new RewardCalculated($request->userId, $calculated));
        } catch (Throwable $e) {
            return $this->failResult($request, $startTime, $e);
        }

        // ── Stage 3: Distribute (DB writes) ──────────────────────────────────
        $distributionResults = [];

        try {
            $result = $this->distributeAction->execute($request, $context);
            $distributionResults[] = $result;

            Event::dispatch(new RewardDistributed($request->userId, $request->rewardType, $result));
        } catch (Throwable $e) {
            // Attempt rollback before re-throwing
            $this->attemptRollback($request, $context);

            return $this->failResult($request, $startTime, $e, $distributionResults);
        }

        // ── Stage 4: Record audit history ─────────────────────────────────────
        if (! $request->dryRun) {
            try {
                $this->recordHistoryAction->execute($request, $distributionResults);
            } catch (Throwable $e) {
                // History write failure should not abort an already-distributed reward.
                // Log and continue — the xp_logs/coin_transactions are the source of truth.
                Log::error('[RewardPipeline] Failed to write reward_history — reward was distributed', [
                    'user_id' => $request->userId,
                    'key'     => $request->idempotencyKey,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        // ── Stage 5: Success event + result ──────────────────────────────────
        $totalXP    = array_sum(array_map(fn ($r) => $r->xpGranted, $distributionResults));
        $totalCoins = array_sum(array_map(fn ($r) => $r->coinsGranted, $distributionResults));
        $elapsed    = $this->elapsed($startTime);

        $engineResult = new RewardEngineResult(
            idempotencyKey:      $request->idempotencyKey,
            userId:              $request->userId,
            status:              RewardStatus::Recorded,
            rewardType:          $request->rewardType,
            totalXPGranted:      $totalXP,
            totalCoinsGranted:   $totalCoins,
            distributionResults: $distributionResults,
            processingTimeMs:    $elapsed,
        );

        Event::dispatch(new RewardGranted(
            $request->userId,
            $request->rewardType,
            $request->source,
            $request->idempotencyKey,
            $totalXP,
            $totalCoins,
            $engineResult,
        ));

        Log::info('[RewardPipeline] Reward granted', [
            'user_id'     => $request->userId,
            'type'        => $request->rewardType->value,
            'source'      => $request->source->value,
            'xp'          => $totalXP,
            'coins_paise' => $totalCoins,
            'ms'          => $elapsed,
        ]);

        return $engineResult;
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function attemptRollback(RewardRequest $request, RewardContext $context): void
    {
        try {
            $this->rollbackAction->execute($request->idempotencyKey, $context);

            Event::dispatch(new RewardRolledBack(
                $request->userId,
                $request->rewardType,
                $request->idempotencyKey,
                'Distribution failed — compensating rollback applied.',
            ));
        } catch (Throwable $rbEx) {
            Log::critical('[RewardPipeline] ROLLBACK FAILED — manual reconciliation required', [
                'user_id'         => $request->userId,
                'idempotency_key' => $request->idempotencyKey,
                'rollback_error'  => $rbEx->getMessage(),
            ]);
        }
    }

    /**
     * @param  DistributionResult[]  $partial
     */
    private function failResult(
        RewardRequest $request,
        float         $startTime,
        Throwable     $e,
        array         $partial = [],
    ): RewardEngineResult {
        $elapsed = $this->elapsed($startTime);

        Log::error('[RewardPipeline] Reward failed', [
            'user_id'   => $request->userId,
            'key'       => $request->idempotencyKey,
            'exception' => $e::class,
            'message'   => $e->getMessage(),
        ]);

        Event::dispatch(new RewardFailed(
            $request->userId,
            $request->rewardType,
            $request->source,
            $request->idempotencyKey,
            $e instanceof RewardEngineException ? $e->errorCode() : 'unexpected',
            $e->getMessage(),
            $e,
        ));

        return new RewardEngineResult(
            idempotencyKey:      $request->idempotencyKey,
            userId:              $request->userId,
            status:              RewardStatus::Failed,
            rewardType:          $request->rewardType,
            totalXPGranted:      0,
            totalCoinsGranted:   0,
            distributionResults: $partial,
            failureReason:       $e->getMessage(),
            processingTimeMs:    $elapsed,
        );
    }

    private function elapsed(float $startTime): float
    {
        return round((microtime(true) - $startTime) * 1000, 2);
    }
}
