<?php
declare(strict_types=1);

namespace App\RewardEngine\Enums;

/**
 * The gameplay system that originated a reward request.
 *
 * Used for audit logging, anti-abuse detection, and routing to the correct
 * validator chain. Every reward must declare its source.
 */
enum RewardSource: string
{
    case Mission        = 'mission';
    case Achievement    = 'achievement';
    case LevelUp        = 'level_up';
    case DailyLogin     = 'daily_login';
    case Season         = 'season';
    case Referral       = 'referral';
    case Admin          = 'admin';
    case Trade          = 'trade';
    case Streak         = 'streak';
    case StoreRefund    = 'store_refund';
    case FeatureUnlock  = 'feature_unlock';
    case Tutorial       = 'tutorial';
    case Event          = 'event';       // Time-limited special events

    /**
     * Sources that must be validated against daily limits.
     */
    public function hasDailyLimit(): bool
    {
        return match ($this) {
            self::Trade,
            self::DailyLogin,
            self::Streak    => true,
            default          => false,
        };
    }

    /**
     * Sources subject to referral-abuse checks.
     */
    public function requiresReferralCheck(): bool
    {
        return $this === self::Referral;
    }

    /**
     * Sources that bypass most validators (admin grants always push through).
     */
    public function bypassesValidation(): bool
    {
        return $this === self::Admin;
    }
}
