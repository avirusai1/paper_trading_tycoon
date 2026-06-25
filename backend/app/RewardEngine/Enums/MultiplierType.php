<?php
declare(strict_types=1);

namespace App\RewardEngine\Enums;

/**
 * Multiplier categories used during reward calculation.
 *
 * Multipliers are stacked multiplicatively by the MultiplierResolver.
 * Each type has a corresponding Rules Engine key prefix.
 */
enum MultiplierType: string
{
    case XP           = 'xp';
    case Coins        = 'coins';
    case PremiumXP    = 'premium_xp';
    case PremiumCoins = 'premium_coins';
    case WeekendXP    = 'weekend_xp';
    case WeekendCoins = 'weekend_coins';
    case SeasonBonus  = 'season_bonus';
    case ReferralBonus = 'referral_bonus';
    case StreakBonus   = 'streak_bonus';

    /**
     * Rules Engine key to look up the multiplier value.
     * e.g. 'rewards.multiplier.premium_xp'
     */
    public function ruleKey(): string
    {
        return 'rewards.multiplier.' . $this->value;
    }
}
