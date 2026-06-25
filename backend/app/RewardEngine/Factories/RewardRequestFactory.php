<?php
declare(strict_types=1);

namespace App\RewardEngine\Factories;

use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardType;

/**
 * Convenience factory for the most common RewardRequest configurations.
 *
 * Callers outside the Reward Engine (Game Engine pipeline, mission/achievement
 * processors) should use these factory methods to construct requests without
 * knowing the idempotency key convention.
 *
 * All methods return fully immutable RewardRequest instances.
 */
final class RewardRequestFactory
{
    public static function missionCoins(int $userId, int $missionId, ?int $overridePaise = null): RewardRequest
    {
        return RewardRequest::make(
            userId:         $userId,
            rewardType:     RewardType::Coins,
            source:         RewardSource::Mission,
            sourceId:       (string) $missionId,
            metadata:       ['source' => RewardSource::Mission->value],
            overrideAmount: $overridePaise,
        );
    }

    public static function missionXP(int $userId, int $missionId, ?int $overrideXP = null): RewardRequest
    {
        return RewardRequest::make(
            userId:         $userId,
            rewardType:     RewardType::XP,
            source:         RewardSource::Mission,
            sourceId:       (string) $missionId,
            overrideAmount: $overrideXP,
        );
    }

    public static function achievementCoins(int $userId, int $achievementId, ?int $overridePaise = null): RewardRequest
    {
        return RewardRequest::make(
            userId:         $userId,
            rewardType:     RewardType::Coins,
            source:         RewardSource::Achievement,
            sourceId:       (string) $achievementId,
            metadata:       ['source' => RewardSource::Achievement->value],
            overrideAmount: $overridePaise,
        );
    }

    public static function achievementBadge(int $userId, int $achievementId, int $storeItemId): RewardRequest
    {
        return RewardRequest::make(
            userId:     $userId,
            rewardType: RewardType::Badge,
            source:     RewardSource::Achievement,
            sourceId:   (string) $achievementId,
            metadata:   ['store_item_id' => $storeItemId],
        );
    }

    public static function levelUpCoins(int $userId, int $levelNumber, ?int $overridePaise = null): RewardRequest
    {
        return RewardRequest::make(
            userId:         $userId,
            rewardType:     RewardType::Coins,
            source:         RewardSource::LevelUp,
            sourceId:       (string) $levelNumber,
            metadata:       ['source' => RewardSource::LevelUp->value],
            overrideAmount: $overridePaise,
        );
    }

    public static function levelUpCareer(int $userId, int $levelNumber): RewardRequest
    {
        return RewardRequest::make(
            userId:     $userId,
            rewardType: RewardType::CareerUnlock,
            source:     RewardSource::LevelUp,
            sourceId:   (string) $levelNumber,
        );
    }

    public static function dailyLoginCoins(int $userId, string $date): RewardRequest
    {
        return RewardRequest::make(
            userId:     $userId,
            rewardType: RewardType::Coins,
            source:     RewardSource::DailyLogin,
            sourceId:   $date,
            metadata:   [
                'source'            => RewardSource::DailyLogin->value,
                'daily_limit_count' => 1,
            ],
        );
    }

    public static function seasonEnd(
        int    $userId,
        int    $seasonId,
        int    $leagueId,
        int    $finalRank,
    ): RewardRequest {
        return RewardRequest::make(
            userId:     $userId,
            rewardType: RewardType::SeasonReward,
            source:     RewardSource::Season,
            sourceId:   "{$seasonId}:{$leagueId}:{$finalRank}",
            metadata:   [
                'season_id'  => $seasonId,
                'league_id'  => $leagueId,
                'final_rank' => $finalRank,
            ],
        );
    }

    public static function referralReward(
        int    $userId,
        int    $referralId,
        string $role, // 'referrer' | 'referred'
        int    $referrerId,
    ): RewardRequest {
        return RewardRequest::make(
            userId:     $userId,
            rewardType: RewardType::ReferralReward,
            source:     RewardSource::Referral,
            sourceId:   (string) $referralId,
            metadata:   [
                'referral_id'   => $referralId,
                'referral_role' => $role,
                'referrer_id'   => $referrerId,
            ],
        );
    }

    public static function adminGrant(
        int     $userId,
        RewardType $rewardType,
        string  $reason,
        int     $amount,
    ): RewardRequest {
        return RewardRequest::make(
            userId:         $userId,
            rewardType:     $rewardType,
            source:         RewardSource::Admin,
            sourceId:       md5($reason . $userId . time()),
            metadata:       ['reason' => $reason],
            overrideAmount: $amount,
        );
    }
}
