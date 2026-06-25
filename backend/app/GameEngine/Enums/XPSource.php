<?php
declare(strict_types=1);

namespace App\GameEngine\Enums;

/**
 * All valid XP grant sources.
 *
 * The string value is used as the `source` column in xp_logs and as the
 * key suffix when looking up base XP amounts from the Rules Engine
 * (e.g. XPSource::TradeBuy → 'xp.trade_buy').
 */
enum XPSource: string
{
    case TradeBuy             = 'trade_buy';
    case TradeSell            = 'trade_sell';
    case DailyLogin           = 'daily_login';
    case MissionCompleted     = 'mission_completed';
    case AchievementUnlocked  = 'achievement_unlocked';
    case ReferralJoined       = 'referral_joined';
    case FirstTrade           = 'first_trade';
    case SeasonReward         = 'season_reward';
    case AdminGrant           = 'admin_grant';

    /**
     * The Rules Engine key used to read the base XP value for this source.
     */
    public function ruleKey(): string
    {
        return 'xp.' . $this->value;
    }

    /**
     * The Rules Engine key used to read the daily XP cap for this source.
     * Returns null for sources without a daily cap.
     */
    public function dailyCapRuleKey(): ?string
    {
        return match ($this) {
            self::TradeBuy   => 'xp.daily_cap.trade_buy',
            self::TradeSell  => 'xp.daily_cap.trade_sell',
            default          => null,
        };
    }

    /**
     * Whether this source enforces a daily per-user XP cap.
     */
    public function hasDailyCap(): bool
    {
        return $this->dailyCapRuleKey() !== null;
    }
}
