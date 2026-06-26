<?php

declare(strict_types=1);

namespace App\RewardEngine\Enums;

/**
 * Machine-readable reasons a reward request failed validation.
 *
 * Used in RewardValidationException and in structured log output.
 * Flutter client maps these codes to localised error messages.
 */
enum ValidationFailureReason: string
{
    case Duplicate = 'duplicate_reward';
    case Expired = 'reward_expired';
    case FeatureDisabled = 'feature_disabled';
    case PremiumOnly = 'premium_only';
    case DailyLimitHit = 'daily_limit_hit';
    case InvalidSeason = 'invalid_season';
    case InvalidMission = 'invalid_mission';
    case ReferralAbuse = 'referral_abuse';
    case UserBanned = 'user_banned';
    case UserSuspended = 'user_suspended';
    case RewardDisabled = 'reward_disabled';
    case LevelRequirement = 'level_requirement_not_met';
    case ItemUnavailable = 'item_unavailable';
}
