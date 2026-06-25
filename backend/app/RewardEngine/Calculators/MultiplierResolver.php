<?php
declare(strict_types=1);

namespace App\RewardEngine\Calculators;

use App\GameEngine\Contracts\GameRuleProviderContract;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\MultiplierResolverContract;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\MultiplierType;

/**
 * Resolves all applicable reward multipliers for a given context.
 *
 * Resolution order (all stacked multiplicatively):
 *   1. Premium XP/Coin bonus (from Rules Engine if user is premium)
 *   2. Weekend bonus (if today is Saturday or Sunday in IST)
 *   3. Season bonus (if active season has a bonus multiplier configured)
 *   4. Equipped item effects (e.g. an xp_boost item in inventory)
 *   5. Streak bonus (if metadata carries a streak multiplier)
 *
 * All base values come from the Rules Engine. No hardcoded constants.
 */
final class MultiplierResolver implements MultiplierResolverContract
{
    public function __construct(
        private readonly GameRuleProviderContract $rules,
    ) {}

    public function resolve(MultiplierType $type, RewardContext $context): float
    {
        $multiplier = 1.0;

        foreach ($this->activeMultipliers($type, $context) as $value) {
            $multiplier *= $value;
        }

        return max(1.0, $multiplier);
    }

    /**
     * @return array<string, float>
     */
    public function breakdown(RewardRequest $request, RewardContext $context): array
    {
        $isXP = in_array($request->rewardType->value, ['xp', 'season_reward', 'referral_reward'], true);
        $type = $isXP ? MultiplierType::XP : MultiplierType::Coins;

        $result = [];

        if ($context->isPremium) {
            $premiumType = $isXP ? MultiplierType::PremiumXP : MultiplierType::PremiumCoins;
            $val = $this->rules->getFloat($premiumType->ruleKey(), 1.0);
            if ($val > 1.0) {
                $result[$premiumType->value] = $val;
            }
        }

        if ($context->isWeekend) {
            $weekendType = $isXP ? MultiplierType::WeekendXP : MultiplierType::WeekendCoins;
            $val = $this->rules->getFloat($weekendType->ruleKey(), 1.0);
            if ($val > 1.0) {
                $result[$weekendType->value] = $val;
            }
        }

        if ($context->hasActiveSeason()) {
            $val = $this->rules->getFloat(MultiplierType::SeasonBonus->ruleKey(), 1.0);
            if ($val > 1.0) {
                $result[MultiplierType::SeasonBonus->value] = $val;
            }
        }

        // Equipped item effects
        $effectKey = $isXP ? 'xp_boost' : 'coin_boost';
        $itemEffect = $context->getItemEffectValue($effectKey);
        if ($itemEffect !== null && (float) $itemEffect > 1.0) {
            $result['item_boost'] = (float) $itemEffect;
        }

        // Streak bonus from metadata
        $streakMultiplier = (float) ($request->metadata['streak_multiplier'] ?? 1.0);
        if ($streakMultiplier > 1.0) {
            $result[MultiplierType::StreakBonus->value] = $streakMultiplier;
        }

        return $result;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * @return array<int, float>
     */
    private function activeMultipliers(MultiplierType $type, RewardContext $context): array
    {
        $values = [];

        // Premium bonus
        if ($context->isPremium) {
            $premiumType = match ($type) {
                MultiplierType::XP     => MultiplierType::PremiumXP,
                MultiplierType::Coins  => MultiplierType::PremiumCoins,
                default                => null,
            };
            if ($premiumType !== null) {
                $values[] = $this->rules->getFloat($premiumType->ruleKey(), 1.0);
            }
        }

        // Weekend bonus
        if ($context->isWeekend) {
            $weekendType = match ($type) {
                MultiplierType::XP     => MultiplierType::WeekendXP,
                MultiplierType::Coins  => MultiplierType::WeekendCoins,
                default                => null,
            };
            if ($weekendType !== null) {
                $values[] = $this->rules->getFloat($weekendType->ruleKey(), 1.0);
            }
        }

        // Season bonus
        if ($context->hasActiveSeason() && $type === MultiplierType::SeasonBonus) {
            $values[] = $this->rules->getFloat(MultiplierType::SeasonBonus->ruleKey(), 1.0);
        }

        // Equipped item effects
        $effectKey = match ($type) {
            MultiplierType::XP    => 'xp_boost',
            MultiplierType::Coins => 'coin_boost',
            default               => null,
        };
        if ($effectKey !== null) {
            $itemEffect = $context->getItemEffectValue($effectKey);
            if ($itemEffect !== null) {
                $values[] = max(1.0, (float) $itemEffect);
            }
        }

        return $values;
    }
}
