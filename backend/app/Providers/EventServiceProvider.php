<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\AchievementUnlocked;
use App\Events\ChallengeCompleted;
use App\Events\CoinsAwarded;
use App\Events\LevelUp;
use App\Events\PortfolioUpdated;
use App\Events\TradeExecuted;
use App\Events\UserRegistered;
use App\Events\XPGranted;
use App\Listeners\Achievement\HandleAchievementUnlockedForRewards;
use App\Listeners\Analytics\HandleTradeExecutedAnalytics;
use App\Listeners\Analytics\HandleUserRegisteredAnalytics;
use App\Listeners\AntiCheat\HandleCoinsAwardedAntiCheat;
use App\Listeners\AntiCheat\HandleTradeExecutedAntiCheat;
use App\Listeners\Game\HandleChallengeCompletedForRewards;
use App\Listeners\Game\HandleLevelUpForAchievements;
use App\Listeners\Game\HandleXPGrantedForChallenges;
use App\Listeners\Notification\HandleAchievementNotification;
use App\Listeners\Notification\HandleChallengeNotification;
use App\Listeners\Notification\HandleLevelUpNotification;
use App\Listeners\Portfolio\HandlePortfolioUpdatedForGame;
use App\Listeners\Portfolio\HandlePortfolioUpdatedForLeaderboard;
use App\Listeners\Trading\HandleTradeExecutedForPortfolio;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Paper Trading Tycoon — Event Service Provider
 *
 * Registers the complete domain event subscriber matrix from
 * 00_MASTER_ARCHITECTURE.md Section 4 (Event Subscriber Matrix).
 *
 * All listeners implement ShouldQueue and run asynchronously.
 * The order of listeners within an event array does NOT guarantee
 * execution order — design listeners to be independent.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * Domain event → queued listener mappings.
     *
     * @var array<class-string, array<class-string>>
     */
    protected $listen = [
        // ── TradeExecuted ──────────────────────────────────────────────────
        TradeExecuted::class => [
            HandleTradeExecutedForPortfolio::class,
            HandleTradeExecutedAntiCheat::class,
            HandleTradeExecutedAnalytics::class,
        ],

        // ── PortfolioUpdated ──────────────────────────────────────────────
        PortfolioUpdated::class => [
            HandlePortfolioUpdatedForGame::class,
            HandlePortfolioUpdatedForLeaderboard::class,
        ],

        // ── XPGranted ─────────────────────────────────────────────────────
        XPGranted::class => [
            HandleXPGrantedForChallenges::class,
        ],

        // ── LevelUp ───────────────────────────────────────────────────────
        LevelUp::class => [
            HandleLevelUpForAchievements::class,
            HandleLevelUpNotification::class,
        ],

        // ── ChallengeCompleted ────────────────────────────────────────────
        ChallengeCompleted::class => [
            HandleChallengeCompletedForRewards::class,
            HandleChallengeNotification::class,
        ],

        // ── AchievementUnlocked ───────────────────────────────────────────
        AchievementUnlocked::class => [
            HandleAchievementUnlockedForRewards::class,
            HandleAchievementNotification::class,
        ],

        // ── CoinsAwarded ──────────────────────────────────────────────────
        CoinsAwarded::class => [
            HandleCoinsAwardedAntiCheat::class,
        ],

        // ── UserRegistered ────────────────────────────────────────────────
        UserRegistered::class => [
            HandleUserRegisteredAnalytics::class,
        ],
    ];

    /**
     * Auto-discover additional listeners in the Listeners directory.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false; // Explicit registration above — no discovery surprises.
    }
}
