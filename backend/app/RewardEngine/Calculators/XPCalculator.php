<?php

declare(strict_types=1);

namespace App\RewardEngine\Calculators;

use App\GameEngine\Contracts\GameRuleProviderContract;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\MultiplierResolverContract;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\RewardType;
use App\RewardEngine\Exceptions\RewardCalculationException;

/**
 * Calculates the final XP amount for XP-type reward requests.
 *
 * Base amount lookup key: 'rewards.xp.{source.value}'
 * e.g. 'rewards.xp.mission', 'rewards.xp.achievement', 'rewards.xp.daily_login'
 *
 * All values from the Rules Engine. Override via RewardRequest::overrideAmount.
 */
final class XPCalculator
{
    public function __construct(
        private readonly GameRuleProviderContract $rules,
        private readonly MultiplierResolverContract $multiplierResolver,
    ) {}

    /**
     * @throws RewardCalculationException
     */
    public function calculate(RewardRequest $request, RewardContext $context): CalculatedReward
    {
        // Override amount — admin grants or test scaffolding
        if ($request->overrideAmount !== null) {
            $baseXP = $request->overrideAmount;
        } else {
            $ruleKey = 'rewards.xp.'.$request->source->value;
            $baseXP = $this->rules->getInt($ruleKey, -1);

            if ($baseXP < 0) {
                throw RewardCalculationException::missingRule($ruleKey);
            }
        }

        // Resolve multipliers
        $multiplierBreakdown = $this->multiplierResolver->breakdown($request, $context);

        $totalMultiplier = array_product(array_values($multiplierBreakdown)) ?: 1.0;
        $totalMultiplier = max(1.0, $totalMultiplier);

        // Apply: use bcmath to stay integer-safe, then round to int
        $finalXP = (int) round($baseXP * $totalMultiplier);

        if ($finalXP < 0) {
            throw RewardCalculationException::negativeAmount('xp', $finalXP);
        }

        return new CalculatedReward(
            rewardType: RewardType::XP,
            idempotencyKey: $request->idempotencyKey,
            userId: $request->userId,
            baseXP: $baseXP,
            finalXP: $finalXP,
            totalMultiplier: $totalMultiplier,
            multiplierBreakdown: $multiplierBreakdown,
            isDryRun: $request->dryRun,
        );
    }
}
