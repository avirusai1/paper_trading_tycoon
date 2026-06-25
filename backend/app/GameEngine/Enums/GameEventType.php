<?php
declare(strict_types=1);

namespace App\GameEngine\Enums;

/**
 * Canonical set of gameplay event types that the Game Engine can process.
 *
 * Each type maps to one or more pipeline steps. The pipeline is responsible
 * for determining which processors are applicable for a given event type.
 */
enum GameEventType: string
{
    case TradeExecuted        = 'trade_executed';
    case DailyLoginCompleted  = 'daily_login_completed';
    case MissionCompleted     = 'mission_completed';
    case AchievementUnlocked  = 'achievement_unlocked';
    case ReferralCompleted    = 'referral_completed';
    case SeasonEnded          = 'season_ended';
    case PortfolioSnapshot    = 'portfolio_snapshot';
    case UserRegistered       = 'user_registered';
    case LevelUp              = 'level_up';

    /**
     * Returns true if this event type should trigger XP processing.
     */
    public function grantsXP(): bool
    {
        return match ($this) {
            self::TradeExecuted,
            self::DailyLoginCompleted,
            self::MissionCompleted,
            self::AchievementUnlocked,
            self::ReferralCompleted,
            self::SeasonEnded   => true,
            default             => false,
        };
    }

    /**
     * Returns true if this event type should trigger mission progress evaluation.
     */
    public function triggersMissions(): bool
    {
        return match ($this) {
            self::TradeExecuted,
            self::DailyLoginCompleted,
            self::ReferralCompleted => true,
            default                 => false,
        };
    }

    /**
     * Returns true if this event should trigger achievement evaluation.
     */
    public function triggersAchievements(): bool
    {
        return match ($this) {
            self::TradeExecuted,
            self::DailyLoginCompleted,
            self::MissionCompleted,
            self::LevelUp            => true,
            default                  => false,
        };
    }
}
