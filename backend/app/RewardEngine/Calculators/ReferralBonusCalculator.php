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
 * Calculates referral rewards for both the referrer and the referred user.
 *
 * Uses separate Rules Engine keys for each role:
 *   'rewards.referral.referrer_xp'    XP granted to the person who shared
 *   'rewards.referral.referrer_coins'  Coins (paise) granted to the person who shared
 *   'rewards.referral.referred_xp'    XP granted to the new user
 *   'rewards.referral.referred_coins'  Coins (paise) granted to the new user
 *
 * Required metadata key: 'referral_role' → 'referrer' | 'referred'
 */
final class ReferralBonusCalculator
{
    public function __construct(
        private readonly GameRuleProviderContract $rules,
    ) {}

    /**
     * @throws RewardCalculationException
     */
    public function calculate(RewardRequest $request, RewardContext $context): CalculatedReward
    {
        $role = $request->meta('referral_role', 'referred');

        $xpKey    = "rewards.referral.{$role}_xp";
        $coinsKey = "rewards.referral.{$role}_coins";

        $baseXP    = $this->rules->getInt($xpKey, -1);
        $baseCoins = $this->rules->getInt($coinsKey, -1);

        if ($baseXP < 0) {
            throw RewardCalculationException::missingRule($xpKey);
        }
        if ($baseCoins < 0) {
            throw RewardCalculationException::missingRule($coinsKey);
        }

        return new CalculatedReward(
            rewardType:     RewardType::ReferralReward,
            idempotencyKey: $request->idempotencyKey,
            userId:         $request->userId,
            baseXP:         $baseXP,
            finalXP:        $baseXP,
            baseCoins:      $baseCoins,
            finalCoins:     $baseCoins,
            isDryRun:       $request->dryRun,
        );
    }
}
