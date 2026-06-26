<?php

declare(strict_types=1);

namespace App\RewardEngine\Actions;

use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardStrategyRegistryContract;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\DistributionResult;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Exceptions\RewardDistributionException;
use App\RewardEngine\Exceptions\RewardEngineException;
use Illuminate\Support\Facades\Log;

/**
 * Action: select strategy → calculate → distribute.
 *
 * Encapsulates the calculate + distribute half of the pipeline. Called by
 * RewardPipeline after the validator chain passes. Delegates to the
 * registered strategy for the request's RewardType.
 *
 * Returns DistributionResult. Throws on hard failures.
 */
class DistributeRewardAction
{
    public function __construct(
        private readonly RewardStrategyRegistryContract $registry,
    ) {}

    /**
     * @throws RewardEngineException
     * @throws RewardDistributionException
     */
    public function execute(RewardRequest $request, RewardContext $context): DistributionResult
    {
        $strategy = $this->registry->get($request->rewardType);

        Log::info('[RewardEngine:DistributeAction] Distributing', [
            'user_id' => $request->userId,
            'reward_type' => $request->rewardType->value,
            'source' => $request->source->value,
            'key' => $request->idempotencyKey,
            'dry_run' => $request->dryRun,
        ]);

        // Calculate
        $calculated = $strategy->calculate($request, $context);

        // Distribute (or dry-run no-op)
        $strategyResult = $strategy->distribute($calculated, $context);

        return new DistributionResult(
            rewardType: $strategyResult->rewardType,
            status: $strategyResult->status,
            idempotencyKey: $strategyResult->idempotencyKey,
            userId: $strategyResult->userId,
            xpGranted: $strategyResult->xpGranted,
            coinsGranted: $strategyResult->coinsGranted,
            extras: $strategyResult->extras,
            wasIdempotent: $strategyResult->wasIdempotent ?? false,
            failureReason: $strategyResult->failureReason,
        );
    }

    /**
     * Public accessor so the pipeline can surface calculated amounts before commit.
     */
    public function calculateOnly(RewardRequest $request, RewardContext $context): CalculatedReward
    {
        return $this->registry->get($request->rewardType)->calculate($request, $context);
    }
}
