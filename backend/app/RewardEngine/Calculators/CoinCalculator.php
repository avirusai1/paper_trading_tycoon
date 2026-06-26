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
 * Calculates the final coin amount for Coin-type reward requests.
 *
 * Amounts are in PAISE (₹1 = 100 paise, stored as BIGINT UNSIGNED).
 *
 * Base amount lookup key: 'rewards.coins.{source.value}'
 * e.g. 'rewards.coins.mission', 'rewards.coins.achievement'
 *
 * The Rules Engine stores coin amounts in paise. No float arithmetic.
 * Multiplier application uses bcmath to remain integer-safe.
 */
final class CoinCalculator
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
        // Override amount — paise
        if ($request->overrideAmount !== null) {
            $baseCoins = $request->overrideAmount;
        } else {
            $ruleKey = 'rewards.coins.'.$request->source->value;
            $baseCoins = $this->rules->getInt($ruleKey, -1);

            if ($baseCoins < 0) {
                throw RewardCalculationException::missingRule($ruleKey);
            }
        }

        // Resolve multipliers
        $multiplierBreakdown = $this->multiplierResolver->breakdown($request, $context);

        $totalMultiplier = array_product(array_values($multiplierBreakdown)) ?: 1.0;
        $totalMultiplier = max(1.0, $totalMultiplier);

        // bcmath multiplication — avoids float rounding on large paise values
        $finalCoins = (int) bcmul((string) $baseCoins, number_format($totalMultiplier, 10, '.', ''), 0);

        if ($finalCoins < 0) {
            throw RewardCalculationException::negativeAmount('coins', $finalCoins);
        }

        return new CalculatedReward(
            rewardType: RewardType::Coins,
            idempotencyKey: $request->idempotencyKey,
            userId: $request->userId,
            baseCoins: $baseCoins,
            finalCoins: $finalCoins,
            totalMultiplier: $totalMultiplier,
            multiplierBreakdown: $multiplierBreakdown,
            isDryRun: $request->dryRun,
        );
    }
}
