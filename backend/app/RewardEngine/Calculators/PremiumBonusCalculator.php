<?php

declare(strict_types=1);

namespace App\RewardEngine\Calculators;

use App\GameEngine\Contracts\GameRuleProviderContract;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\RewardType;
use App\RewardEngine\Exceptions\RewardCalculationException;

/**
 * Calculates the additional coin bonus granted to premium users.
 *
 * Used by PremiumBonusStrategy. Reads the bonus paise amount from the
 * Rules Engine key: 'rewards.premium.bonus_coins'
 *
 * Returns zero if the user is not premium (caller is responsible for
 * checking context.isPremium before invoking, but this class is safe
 * to call regardless — it will return a zero CalculatedReward).
 */
final class PremiumBonusCalculator
{
    public function __construct(
        private readonly GameRuleProviderContract $rules,
    ) {}

    /**
     * @throws RewardCalculationException
     */
    public function calculate(RewardRequest $request, RewardContext $context): CalculatedReward
    {
        if (! $context->isPremium) {
            return new CalculatedReward(
                rewardType: RewardType::PremiumBonus,
                idempotencyKey: $request->idempotencyKey,
                userId: $request->userId,
                isDryRun: $request->dryRun,
            );
        }

        $bonusCoins = $this->rules->getInt('rewards.premium.bonus_coins', -1);

        if ($bonusCoins < 0) {
            throw RewardCalculationException::missingRule('rewards.premium.bonus_coins');
        }

        return new CalculatedReward(
            rewardType: RewardType::PremiumBonus,
            idempotencyKey: $request->idempotencyKey.':premium',
            userId: $request->userId,
            baseCoins: $bonusCoins,
            finalCoins: $bonusCoins,
            isDryRun: $request->dryRun,
        );
    }
}
