<?php
declare(strict_types=1);

namespace App\GameEngine\Factories;

use App\GameEngine\Contracts\GameContextBuilderContract;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\Enums\PlayerState;
use App\GameEngine\Exceptions\GameEngineException;
use App\Models\FeatureFlag;
use App\Models\League;
use App\Models\Season;
use App\Models\StoreItem;
use App\Models\User;
use App\Models\UserInventory;
use App\Models\UserLeague;
use App\Models\UserLevel;
use App\Models\UserMission;
use App\Models\Wallet;

/**
 * Builds a complete, hydrated GameContext for a given user.
 *
 * This is the most DB-intensive operation in the Game Engine — it must load
 * every piece of state needed by the entire pipeline in as few queries as
 * possible.
 *
 * Query plan (8 queries in V1 — optimize with eager loading as needed):
 * 1. User (with profile for streak)
 * 2. Wallet
 * 3. UserLevel
 * 4. Active season
 * 5. UserLeague for active season (+ League)
 * 6. Active UserMissions (with mission relationship)
 * 7. Unlocked achievement IDs
 * 8. Equipped inventory items (for multipliers)
 * 9. Feature flags (cached by FeatureFlagService)
 */
final class GameContextBuilder implements GameContextBuilderContract
{
    public function build(int $userId): GameContext
    {
        $user = User::with('profile')->find($userId);

        if ($user === null) {
            throw new GameEngineException(
                "Cannot build GameContext — user {$userId} not found.",
                'context_user_not_found',
            );
        }

        $wallet    = Wallet::where('user_id', $userId)->first();
        $userLevel = UserLevel::where('user_id', $userId)->first();

        if ($wallet === null || $userLevel === null) {
            throw new GameEngineException(
                "User {$userId} is missing wallet or level record. Ensure user registration completed.",
                'context_missing_records',
            );
        }

        $activeSeason  = Season::where('status', 'active')->latest('starts_at')->first();
        $userLeague    = null;
        $league        = null;

        if ($activeSeason !== null) {
            $userLeague = UserLeague::where('user_id', $userId)
                ->where('season_id', $activeSeason->id)
                ->first();

            if ($userLeague !== null) {
                $league = League::find($userLeague->league_id);
            }
        }

        $activeMissions = UserMission::with('mission')
            ->where('user_id', $userId)
            ->where('status', 'assigned')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->get()
            ->all();

        $unlockedAchievementIds = \App\Models\UserAchievement::where('user_id', $userId)
            ->pluck('achievement_id')
            ->all();

        $activeMultipliers = $this->resolveMultipliers($userId, $user->is_premium);
        $featureFlags      = $this->resolveFeatureFlags($user);
        $loginStreak       = $user->profile?->login_streak_days ?? 0;

        $playerState = $this->resolvePlayerState($user);

        return new GameContext(
            user:                   $user,
            playerState:            $playerState,
            wallet:                 $wallet,
            userLevel:              $userLevel,
            currentLeague:          $userLeague,
            league:                 $league,
            activeSeason:           $activeSeason,
            activeMissions:         $activeMissions,
            unlockedAchievementIds: $unlockedAchievementIds,
            loginStreakDays:        $loginStreak,
            activeMultipliers:      $activeMultipliers,
            featureFlags:           $featureFlags,
            builtAt:                microtime(true),
        );
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function resolvePlayerState(User $user): PlayerState
    {
        return match ($user->status) {
            'active'    => $user->is_premium ? PlayerState::ActivePremium : PlayerState::Active,
            'suspended' => PlayerState::Suspended,
            'banned'    => PlayerState::Banned,
            default     => PlayerState::Active,
        };
    }

    /**
     * @return array<string, float>
     */
    private function resolveMultipliers(int $userId, bool $isPremium): array
    {
        $xpMultiplier   = 1.0;
        $coinMultiplier = 1.0;

        // Equipped store item effects
        $equippedItems = UserInventory::with('storeItem')
            ->where('user_id', $userId)
            ->where('is_equipped', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->get();

        foreach ($equippedItems as $inventory) {
            $item   = $inventory->storeItem;
            $effect = $item->effect ?? [];

            if (isset($effect['xp_boost'])) {
                $xpMultiplier *= (float) $effect['xp_boost'];
            }

            if (isset($effect['coin_boost'])) {
                $coinMultiplier *= (float) $effect['coin_boost'];
            }
        }

        return [
            'xp'    => $xpMultiplier,
            'coins' => $coinMultiplier,
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function resolveFeatureFlags(User $user): array
    {
        $flags = FeatureFlag::all();
        $map   = [];

        foreach ($flags as $flag) {
            $map[$flag->key] = $flag->isEnabledForUser($user);
        }

        return $map;
    }
}
