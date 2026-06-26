<?php

declare(strict_types=1);

namespace App\RewardEngine\Enums;

/**
 * Every discrete reward type the Reward Engine can distribute.
 *
 * Adding a new reward type requires:
 * 1. A new case here.
 * 2. A new RewardStrategy implementation.
 * 3. Registration in RewardStrategyRegistry.
 * 4. No changes to the pipeline, validators, or distributors.
 *
 * The string backing value is stored in reward_history.source_type and used
 * as a cache/log key, so treat it as a stable public identifier.
 */
enum RewardType: string
{
    case XP = 'xp';
    case Coins = 'coins';
    case InventoryItem = 'inventory_item';
    case CareerUnlock = 'career_unlock';
    case Title = 'title';
    case Badge = 'badge';
    case Avatar = 'avatar';
    case Frame = 'frame';
    case Theme = 'theme';
    case MissionUnlock = 'mission_unlock';
    case FeatureUnlock = 'feature_unlock';
    case PremiumBonus = 'premium_bonus';
    case SeasonReward = 'season_reward';
    case ReferralReward = 'referral_reward';
    case AdminReward = 'admin_reward';

    /**
     * Whether this reward type modifies the wallet (requires ledger entry).
     */
    public function affectsWallet(): bool
    {
        return match ($this) {
            self::Coins, self::PremiumBonus,
            self::SeasonReward, self::ReferralReward,
            self::AdminReward => true,
            default => false,
        };
    }

    /**
     * Whether this reward type modifies the XP ledger.
     */
    public function affectsXP(): bool
    {
        return match ($this) {
            self::XP, self::SeasonReward,
            self::ReferralReward, self::AdminReward => true,
            default => false,
        };
    }

    /**
     * Whether this reward type modifies the user's inventory.
     */
    public function affectsInventory(): bool
    {
        return match ($this) {
            self::InventoryItem, self::Badge,
            self::Avatar, self::Frame, self::Theme => true,
            default => false,
        };
    }

    /**
     * Whether this reward type supports rollback (compensating transaction).
     * Ledger-based rewards (coins, XP) use compensating entries.
     * Inventory grants can be revoked by deleting the inventory record.
     */
    public function supportsRollback(): bool
    {
        return match ($this) {
            self::XP, self::Coins,
            self::InventoryItem, self::Badge,
            self::Avatar, self::Frame, self::Theme,
            self::PremiumBonus, self::SeasonReward,
            self::ReferralReward, self::AdminReward => true,
            default => false,
        };
    }
}
