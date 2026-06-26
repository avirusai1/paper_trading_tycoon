<?php

declare(strict_types=1);

namespace App\RewardEngine\Contexts;

use App\Models\Season;
use App\Models\User;
use App\Models\UserInventory;
use App\Models\UserLeague;
use App\Models\UserLevel;
use App\Models\Wallet;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\MultiplierType;
use Carbon\Carbon;

/**
 * Immutable snapshot of all player state relevant to reward processing.
 *
 * Built once per pipeline run by RewardContextFactory and shared across
 * all validators, calculators, strategies, and distributors in that run.
 *
 * No DB calls happen after context construction — all reads are done upfront.
 * Modifying DB state during pipeline execution does NOT reflect here.
 */
class RewardContext
{
    /**
     * @param  User  $user  The target user model.
     * @param  Wallet  $wallet  Live wallet snapshot.
     * @param  UserLevel  $userLevel  Current XP and level state.
     * @param  Season|null  $activeSeason  Null when no season is active.
     * @param  UserLeague|null  $userLeague  Null when user has no league.
     * @param  array<string,bool>  $featureFlags  ['flag_key' => true/false]
     * @param  array<MultiplierType,float>  $multipliers  Resolved per multiplier type.
     * @param  UserInventory[]  $equippedItems  Only equipped (is_equipped=true) items.
     * @param  bool  $isPremium  Whether user has active premium.
     * @param  bool  $isBanned  Whether user is banned/suspended.
     * @param  Carbon  $builtAt  When context was assembled.
     * @param  bool  $isWeekend  True if current IST day is Sat or Sun.
     * @param  array<string,mixed>  $extra  Forwarded from RewardRequest::metadata.
     */
    public function __construct(
        public readonly User $user,
        public readonly Wallet $wallet,
        public readonly UserLevel $userLevel,
        public readonly ?Season $activeSeason,
        public readonly ?UserLeague $userLeague,
        public readonly array $featureFlags,
        public readonly array $multipliers,
        public readonly array $equippedItems,
        public readonly bool $isPremium,
        public readonly bool $isBanned,
        public readonly Carbon $builtAt,
        public readonly bool $isWeekend,
        public readonly array $extra = [],
    ) {}

    // ── Convenience accessors ─────────────────────────────────────────────────

    public function userId(): int
    {
        return $this->user->id;
    }

    /**
     * Wallet balance in paise.
     */
    public function coinBalance(): int
    {
        return (int) $this->wallet->balance;
    }

    public function currentLevel(): int
    {
        return $this->userLevel->current_level;
    }

    public function currentXP(): int
    {
        return (int) $this->userLevel->current_xp;
    }

    /**
     * Returns the configured multiplier for the given type, defaulting to 1.0.
     */
    public function multiplier(MultiplierType $type): float
    {
        return $this->multipliers[$type->value] ?? 1.0;
    }

    public function hasFeature(string $key): bool
    {
        return $this->featureFlags[$key] ?? false;
    }

    /**
     * Whether a season is currently active and valid.
     */
    public function hasActiveSeason(): bool
    {
        return $this->activeSeason !== null
            && $this->activeSeason->is_active;
    }

    /**
     * Whether the user has any equipped item with the given effect key.
     * e.g. isItemEffectActive('xp_boost')
     */
    public function isItemEffectActive(string $effectKey): bool
    {
        foreach ($this->equippedItems as $item) {
            $effects = $item->storeItem?->effects ?? [];
            if (array_key_exists($effectKey, $effects)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve the first equipped item effect value for the given key, or null.
     */
    public function getItemEffectValue(string $effectKey): float|int|null
    {
        foreach ($this->equippedItems as $item) {
            $effects = $item->storeItem?->effects ?? [];
            if (array_key_exists($effectKey, $effects)) {
                return $effects[$effectKey];
            }
        }

        return null;
    }

    /**
     * Retrieve a metadata value forwarded from the original RewardRequest.
     */
    public function extra(string $key, mixed $default = null): mixed
    {
        return $this->extra[$key] ?? $default;
    }
}
