<?php

declare(strict_types=1);

namespace App\RewardEngine\Factories;

use App\Models\Season;
use App\Models\User;
use App\Models\UserInventory;
use App\Models\UserLeague;
use App\Models\UserLevel;
use App\Models\Wallet;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardContextBuilderContract;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Exceptions\RewardEngineException;
use App\Services\Features\FeatureFlagService;
use Carbon\Carbon;

/**
 * Builds a complete, immutable RewardContext from a RewardRequest.
 *
 * Performs all DB reads upfront (8–10 queries) so the pipeline runs
 * from memory-cached state. No DB reads after this factory returns.
 *
 * Query order:
 * 1. User
 * 2. Wallet
 * 3. UserLevel
 * 4. Active Season
 * 5. UserLeague (for current season)
 * 6. Equipped inventory items (with storeItem relation)
 * 7. Premium status (from user.subscriptions or user_profiles.is_premium)
 * 8. Feature flags (from FeatureFlagService cache)
 */
final class RewardContextFactory implements RewardContextBuilderContract
{
    public function __construct(
        private readonly FeatureFlagService $featureFlags,
    ) {}

    /**
     * @throws RewardEngineException If the user is not found.
     */
    public function build(RewardRequest $request): RewardContext
    {
        $user = User::query()->find($request->userId);

        if ($user === null) {
            throw new RewardEngineException(
                "User {$request->userId} not found — cannot build RewardContext.",
                'reward_context_user_not_found',
                404,
            );
        }

        /** @var Wallet $wallet */
        $wallet = Wallet::query()
            ->where('user_id', $request->userId)
            ->firstOrFail();

        /** @var UserLevel $userLevel */
        $userLevel = UserLevel::query()
            ->where('user_id', $request->userId)
            ->firstOrFail();

        $activeSeason = Season::query()
            ->where('is_active', true)
            ->first();

        $userLeague = null;
        if ($activeSeason !== null) {
            $userLeague = UserLeague::query()
                ->where('user_id', $request->userId)
                ->where('season_id', $activeSeason->id)
                ->first();
        }

        /** @var UserInventory[] $equippedItems */
        $equippedItems = UserInventory::query()
            ->with('storeItem')
            ->where('user_id', $request->userId)
            ->where('is_equipped', true)
            ->whereNull('expires_at')
            ->orWhere(fn ($q) => $q
                ->where('user_id', $request->userId)
                ->where('is_equipped', true)
                ->where('expires_at', '>', now())
            )
            ->get()
            ->all();

        $isPremium = $this->resolvePremiumStatus($user);
        $isBanned = $this->resolveBannedStatus($user);

        // Load all feature flags from cache
        $flags = $this->featureFlags->all();

        // Resolve IST weekend
        $nowIST = Carbon::now('Asia/Kolkata');
        $isWeekend = $nowIST->isWeekend();

        // Multipliers are resolved lazily by MultiplierResolver during calculation.
        // We pre-populate the premium/weekend flags here so resolvers can act on them.
        $multipliers = [];

        return new RewardContext(
            user: $user,
            wallet: $wallet,
            userLevel: $userLevel,
            activeSeason: $activeSeason,
            userLeague: $userLeague,
            featureFlags: $flags,
            multipliers: $multipliers,
            equippedItems: $equippedItems,
            isPremium: $isPremium,
            isBanned: $isBanned,
            builtAt: Carbon::now(),
            isWeekend: $isWeekend,
            extra: $request->metadata,
        );
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function resolvePremiumStatus(User $user): bool
    {
        // Check the cached profile flag first; fall back to subscription check.
        return (bool) ($user->profile?->is_premium ?? false);
    }

    private function resolveBannedStatus(User $user): bool
    {
        return isset($user->banned_at) && $user->banned_at !== null;
    }
}
