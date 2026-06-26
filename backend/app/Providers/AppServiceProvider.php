<?php

declare(strict_types=1);

namespace App\Providers;

use App\GameEngine\Actions\GrantAchievementProgressAction;
use App\GameEngine\Actions\GrantCareerProgressAction;
use App\GameEngine\Actions\GrantCoinsAction;
use App\GameEngine\Actions\GrantLeagueProgressAction;
use App\GameEngine\Actions\GrantMissionProgressAction;
use App\GameEngine\Actions\GrantSeasonProgressAction;
use App\GameEngine\Actions\GrantXPAction;
use App\GameEngine\Contracts\AchievementProcessorContract;
use App\GameEngine\Contracts\CareerProcessorContract;
use App\GameEngine\Contracts\CoinLedgerContract;
use App\GameEngine\Contracts\GameContextBuilderContract;
use App\GameEngine\Contracts\GameEngineContract;
use App\GameEngine\Contracts\GameRuleProviderContract;
use App\GameEngine\Contracts\LeagueProcessorContract;
use App\GameEngine\Contracts\MissionProcessorContract;
use App\GameEngine\Contracts\RewardProcessorContract;
use App\GameEngine\Contracts\SeasonProcessorContract;
use App\GameEngine\Contracts\XPProcessorContract;
use App\GameEngine\Factories\GameContextBuilder;
use App\GameEngine\GameEngine;
use App\GameEngine\Pipelines\GameEventPipeline;
use App\GameEngine\Processors\AchievementProcessor;
use App\GameEngine\Processors\CareerProcessor;
use App\GameEngine\Processors\LeagueProcessor;
use App\GameEngine\Processors\MissionProcessor;
use App\GameEngine\Processors\RewardProcessor;
use App\GameEngine\Processors\SeasonProcessor;
use App\GameEngine\Processors\XPProcessor;
use App\GameEngine\Rules\GameRuleService;
use App\GameEngine\Support\AchievementCriteriaEvaluator;
use App\GameEngine\Support\DailyCapTracker;
use App\GameEngine\Support\MissionCriteriaEvaluator;
use App\GameEngine\Support\XPMultiplierCalculator;
use App\MarketData\Cache\MarketDataCache;
use App\MarketData\Contracts\StockRepositoryContract;
use App\MarketData\Repositories\StockRepository;
use App\MarketData\Services\MarketDataService;
use App\MarketData\Support\ProviderCoordinator;
use App\RewardEngine\Actions\DistributeRewardAction;
use App\RewardEngine\Actions\RecordRewardHistoryAction;
use App\RewardEngine\Actions\RollbackRewardAction;
use App\RewardEngine\Calculators\CoinCalculator;
use App\RewardEngine\Calculators\MultiplierResolver;
use App\RewardEngine\Calculators\PremiumBonusCalculator;
use App\RewardEngine\Calculators\ReferralBonusCalculator;
use App\RewardEngine\Calculators\SeasonBonusCalculator;
use App\RewardEngine\Calculators\XPCalculator;
use App\RewardEngine\Contracts\MultiplierResolverContract;
use App\RewardEngine\Contracts\RewardContextBuilderContract;
use App\RewardEngine\Contracts\RewardEngineContract;
use App\RewardEngine\Contracts\RewardStrategyRegistryContract;
use App\RewardEngine\Factories\RewardContextFactory;
use App\RewardEngine\Pipelines\RewardPipeline;
use App\RewardEngine\Services\RewardEngine;
use App\RewardEngine\Strategies\BadgeRewardStrategy;
use App\RewardEngine\Strategies\CareerRewardStrategy;
use App\RewardEngine\Strategies\CoinRewardStrategy;
use App\RewardEngine\Strategies\InventoryRewardStrategy;
use App\RewardEngine\Strategies\XPRewardStrategy;
use App\RewardEngine\Support\RewardStrategyRegistry;
use App\RewardEngine\Validators\DailyLimitValidator;
use App\RewardEngine\Validators\DuplicateRewardValidator;
use App\RewardEngine\Validators\ExpiredRewardValidator;
use App\RewardEngine\Validators\FeatureGateValidator;
use App\RewardEngine\Validators\PremiumOnlyValidator;
use App\RewardEngine\Validators\ReferralAbuseValidator;
use App\RewardEngine\Validators\SeasonValidityValidator;
use App\RewardEngine\Validators\UserStatusValidator;
use App\Services\Economy\CoinLedgerService;
use App\Services\Features\FeatureFlagService;
use App\Trading\Contracts\HoldingRepositoryContract;
use App\Trading\Contracts\OrderRepositoryContract;
use App\Trading\Contracts\TradeRepositoryContract;
use App\Trading\Contracts\TradingContextFactoryContract;
use App\Trading\Contracts\TradingEngineContract;
use App\Trading\Enums\OrderType;
use App\Trading\Factories\TradingContextFactory;
use App\Trading\Pipelines\TradingPipeline;
use App\Trading\Repositories\HoldingRepository;
use App\Trading\Repositories\OrderRepository;
use App\Trading\Repositories\TradeRepository;
use App\Trading\Services\TradingEngine;
use App\Trading\Strategies\BracketOrderStrategy;
use App\Trading\Strategies\LimitOrderStrategy;
use App\Trading\Strategies\MarketOrderStrategy;
use App\Trading\Strategies\OrderStrategyRegistry;
use App\Trading\Strategies\StopLossStrategy;
use App\Trading\Validators\DuplicateOrderValidator;
use App\Trading\Validators\FeatureFlagValidator;
use App\Trading\Validators\InvalidQuantityValidator;
use App\Trading\Validators\InvalidSymbolValidator;
use App\Trading\Validators\MarketOpenValidator;
use App\Trading\Validators\MaxDailyTradesValidator;
use App\Trading\Validators\MaxExposureValidator;
use App\Trading\Validators\MaxPositionSizeValidator;
use App\Trading\Validators\PremiumFeaturesValidator;
use App\Trading\Validators\SufficientCashValidator;
use App\Trading\Validators\SufficientHoldingsValidator;
use App\Trading\Validators\TradingHoursValidator;
use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider — DI bindings.
 *
 * Singletons (shared instance per request/job):
 * - GameRuleProviderContract → GameRuleService (reads DB with cache)
 * - GameEngineContract       → GameEngine (coordinator)
 * - GameContextBuilderContract → GameContextBuilder
 * - DailyCapTracker          (cache-backed, no benefit to re-creating)
 * - FeatureFlagService       (DB-backed with cache)
 *
 * Transients (new instance per resolve):
 * - All processors (stateless, cheap to create)
 * - All actions (stateless, cheap to create)
 */
final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Singletons ────────────────────────────────────────────────────────

        $this->app->singleton(GameRuleProviderContract::class, GameRuleService::class);
        $this->app->singleton(FeatureFlagService::class, FeatureFlagService::class);
        $this->app->singleton(DailyCapTracker::class, DailyCapTracker::class);

        // Support helpers (singletons — stateless but have injected singletons)
        $this->app->singleton(XPMultiplierCalculator::class, fn ($app) => new XPMultiplierCalculator($app->make(GameRuleProviderContract::class))
        );
        $this->app->singleton(MissionCriteriaEvaluator::class, MissionCriteriaEvaluator::class);
        $this->app->singleton(AchievementCriteriaEvaluator::class, AchievementCriteriaEvaluator::class);

        // ── Actions (transient) ───────────────────────────────────────────────

        $this->app->bind(GrantXPAction::class, fn ($app) => new GrantXPAction(
            $app->make(GameRuleProviderContract::class),
            $app->make(XPMultiplierCalculator::class),
            $app->make(DailyCapTracker::class),
        )
        );

        $this->app->bind(GrantCoinsAction::class, GrantCoinsAction::class);
        $this->app->bind(GrantCareerProgressAction::class, GrantCareerProgressAction::class);

        $this->app->bind(GrantMissionProgressAction::class, fn ($app) => new GrantMissionProgressAction(
            $app->make(MissionCriteriaEvaluator::class),
        )
        );

        $this->app->bind(GrantAchievementProgressAction::class, fn ($app) => new GrantAchievementProgressAction(
            $app->make(AchievementCriteriaEvaluator::class),
        )
        );

        $this->app->bind(GrantLeagueProgressAction::class, GrantLeagueProgressAction::class);

        $this->app->bind(GrantSeasonProgressAction::class, fn ($app) => new GrantSeasonProgressAction(
            $app->make(GrantCoinsAction::class),
            $app->make(GrantXPAction::class),
        )
        );

        // ── Processors (transient, implement contracts) ───────────────────────

        $this->app->bind(XPProcessorContract::class, fn ($app) => new XPProcessor(
            $app->make(GrantXPAction::class),
            $app->make(DailyCapTracker::class),
        )
        );

        $this->app->bind(RewardProcessorContract::class, fn ($app) => new RewardProcessor(
            $app->make(GrantCoinsAction::class),
            $app->make(GameRuleProviderContract::class),
        )
        );

        $this->app->bind(MissionProcessorContract::class, fn ($app) => new MissionProcessor(
            $app->make(GrantMissionProgressAction::class),
            $app->make(GrantXPAction::class),
            $app->make(GrantCoinsAction::class),
        )
        );

        $this->app->bind(CareerProcessorContract::class, fn ($app) => new CareerProcessor($app->make(GrantCareerProgressAction::class))
        );

        $this->app->bind(AchievementProcessorContract::class, fn ($app) => new AchievementProcessor(
            $app->make(GrantAchievementProgressAction::class),
            $app->make(GrantXPAction::class),
            $app->make(GrantCoinsAction::class),
        )
        );

        $this->app->bind(LeagueProcessorContract::class, fn ($app) => new LeagueProcessor($app->make(GrantLeagueProgressAction::class))
        );

        $this->app->bind(SeasonProcessorContract::class, fn ($app) => new SeasonProcessor(
            $app->make(GrantLeagueProgressAction::class),
            $app->make(GrantSeasonProgressAction::class),
        )
        );

        // ── Pipeline (singleton — stateless orchestrator) ─────────────────────

        $this->app->singleton(GameEventPipeline::class, fn ($app) => new GameEventPipeline(
            xpProcessor: $app->make(XPProcessorContract::class),
            careerProcessor: $app->make(CareerProcessorContract::class),
            missionProcessor: $app->make(MissionProcessorContract::class),
            achievementProcessor: $app->make(AchievementProcessorContract::class),
            leagueProcessor: $app->make(LeagueProcessorContract::class),
            seasonProcessor: $app->make(SeasonProcessorContract::class),
            rewardProcessor: $app->make(RewardProcessorContract::class),
        )
        );

        // ── GameContext builder (singleton) ───────────────────────────────────

        $this->app->singleton(GameContextBuilderContract::class, GameContextBuilder::class);
        $this->app->singleton(GameContextBuilder::class, GameContextBuilder::class);

        // ── Central GameEngine (singleton) ────────────────────────────────────

        $this->app->singleton(GameEngineContract::class, fn ($app) => new GameEngine(
            $app->make(GameContextBuilder::class),
            $app->make(GameEventPipeline::class),
        )
        );

        $this->app->singleton(GameEngine::class, fn ($app) => $app->make(GameEngineContract::class)
        );

        // ── Coin Ledger ────────────────────────────────────────────────────────
        $this->app->bind(CoinLedgerContract::class, CoinLedgerService::class);
        $this->app->bind(CoinLedgerService::class, CoinLedgerService::class);

        // ═════════════════════════════════════════════════════════════════════
        // REWARD ENGINE BINDINGS
        // ═════════════════════════════════════════════════════════════════════

        // ── Multiplier resolver (singleton) ───────────────────────────────────
        $this->app->singleton(MultiplierResolverContract::class, fn ($app) => new MultiplierResolver($app->make(GameRuleProviderContract::class))
        );
        $this->app->singleton(MultiplierResolver::class, fn ($app) => $app->make(MultiplierResolverContract::class)
        );

        // ── Calculators (transient) ───────────────────────────────────────────
        $this->app->bind(XPCalculator::class, fn ($app) => new XPCalculator(
            $app->make(GameRuleProviderContract::class),
            $app->make(MultiplierResolver::class),
        )
        );
        $this->app->bind(CoinCalculator::class, fn ($app) => new CoinCalculator(
            $app->make(GameRuleProviderContract::class),
            $app->make(MultiplierResolver::class),
        )
        );
        $this->app->bind(SeasonBonusCalculator::class, fn ($app) => new SeasonBonusCalculator($app->make(GameRuleProviderContract::class))
        );
        $this->app->bind(ReferralBonusCalculator::class, fn ($app) => new ReferralBonusCalculator($app->make(GameRuleProviderContract::class))
        );
        $this->app->bind(PremiumBonusCalculator::class, fn ($app) => new PremiumBonusCalculator($app->make(GameRuleProviderContract::class))
        );

        // ── Strategies (transient) ────────────────────────────────────────────
        $this->app->bind(XPRewardStrategy::class, fn ($app) => new XPRewardStrategy(
            $app->make(XPCalculator::class),
            $app->make(XPProcessorContract::class),
        )
        );
        $this->app->bind(CoinRewardStrategy::class, fn ($app) => new CoinRewardStrategy(
            $app->make(CoinCalculator::class),
            $app->make(CoinLedgerContract::class),
        )
        );
        $this->app->bind(InventoryRewardStrategy::class, InventoryRewardStrategy::class);
        $this->app->bind(CareerRewardStrategy::class, CareerRewardStrategy::class);
        $this->app->bind(BadgeRewardStrategy::class, BadgeRewardStrategy::class);

        // ── Strategy Registry (singleton) ─────────────────────────────────────
        $this->app->singleton(RewardStrategyRegistryContract::class, fn ($app) => new RewardStrategyRegistry([
            $app->make(XPRewardStrategy::class),
            $app->make(CoinRewardStrategy::class),
            $app->make(InventoryRewardStrategy::class),
            $app->make(CareerRewardStrategy::class),
            $app->make(BadgeRewardStrategy::class),
        ])
        );
        $this->app->singleton(RewardStrategyRegistry::class, fn ($app) => $app->make(RewardStrategyRegistryContract::class)
        );

        // ── Actions (transient) ───────────────────────────────────────────────
        $this->app->bind(DistributeRewardAction::class, fn ($app) => new DistributeRewardAction($app->make(RewardStrategyRegistryContract::class))
        );
        $this->app->bind(RollbackRewardAction::class, fn ($app) => new RollbackRewardAction($app->make(RewardStrategyRegistryContract::class))
        );
        $this->app->bind(RecordRewardHistoryAction::class, RecordRewardHistoryAction::class);

        // ── Validators (transient) ────────────────────────────────────────────
        $this->app->bind(DuplicateRewardValidator::class, DuplicateRewardValidator::class);
        $this->app->bind(FeatureGateValidator::class, FeatureGateValidator::class);
        $this->app->bind(PremiumOnlyValidator::class, PremiumOnlyValidator::class);
        $this->app->bind(DailyLimitValidator::class, DailyLimitValidator::class);
        $this->app->bind(SeasonValidityValidator::class, SeasonValidityValidator::class);
        $this->app->bind(ExpiredRewardValidator::class, ExpiredRewardValidator::class);
        $this->app->bind(ReferralAbuseValidator::class, ReferralAbuseValidator::class);
        $this->app->bind(UserStatusValidator::class, UserStatusValidator::class);

        // ── Context builder (singleton) ───────────────────────────────────────
        $this->app->singleton(RewardContextBuilderContract::class, fn ($app) => new RewardContextFactory($app->make(FeatureFlagService::class))
        );
        $this->app->singleton(RewardContextFactory::class, fn ($app) => $app->make(RewardContextBuilderContract::class)
        );

        // ── Reward Pipeline (singleton) ───────────────────────────────────────
        $this->app->singleton(RewardPipeline::class, fn ($app) => new RewardPipeline(
            validators: [
                $app->make(UserStatusValidator::class),
                $app->make(FeatureGateValidator::class),
                $app->make(DuplicateRewardValidator::class),
                $app->make(PremiumOnlyValidator::class),
                $app->make(DailyLimitValidator::class),
                $app->make(SeasonValidityValidator::class),
                $app->make(ExpiredRewardValidator::class),
                $app->make(ReferralAbuseValidator::class),
            ],
            distributeAction: $app->make(DistributeRewardAction::class),
            rollbackAction: $app->make(RollbackRewardAction::class),
            recordHistoryAction: $app->make(RecordRewardHistoryAction::class),
        )
        );

        // ── Central RewardEngine service (singleton) ──────────────────────────
        $this->app->singleton(RewardEngineContract::class, fn ($app) => new RewardEngine(
            $app->make(RewardContextBuilderContract::class),
            $app->make(RewardPipeline::class),
            $app->make(RollbackRewardAction::class),
        )
        );
        $this->app->singleton(RewardEngine::class, fn ($app) => $app->make(RewardEngineContract::class)
        );

        // ═════════════════════════════════════════════════════════════════════
        // MARKET DATA SUB-SYSTEM BINDINGS
        // ═════════════════════════════════════════════════════════════════════
        $this->app->singleton(StockRepositoryContract::class, StockRepository::class);
        $this->app->singleton(MarketDataCache::class, MarketDataCache::class);
        $this->app->singleton(ProviderCoordinator::class, ProviderCoordinator::class);
        $this->app->singleton(MarketDataService::class, MarketDataService::class);

        // ═════════════════════════════════════════════════════════════════════
        // TRADING ENGINE SUB-SYSTEM BINDINGS
        // ═════════════════════════════════════════════════════════════════════
        $this->app->singleton(OrderRepositoryContract::class, OrderRepository::class);
        $this->app->singleton(TradeRepositoryContract::class, TradeRepository::class);
        $this->app->singleton(HoldingRepositoryContract::class, HoldingRepository::class);
        $this->app->singleton(TradingContextFactoryContract::class, TradingContextFactory::class);

        $this->app->singleton(OrderStrategyRegistry::class, fn ($app) => new OrderStrategyRegistry([
            OrderType::Market->value => new MarketOrderStrategy,
            OrderType::Limit->value => new LimitOrderStrategy,
            OrderType::Stop->value => new StopLossStrategy,
            OrderType::Bracket->value => new BracketOrderStrategy,
        ]));

        $this->app->bind(TradingPipeline::class, fn ($app) => new TradingPipeline(
            contextFactory: $app->make(TradingContextFactoryContract::class),
            strategyRegistry: $app->make(OrderStrategyRegistry::class),
            orderRepository: $app->make(OrderRepositoryContract::class),
            tradeRepository: $app->make(TradeRepositoryContract::class),
            holdingRepository: $app->make(HoldingRepositoryContract::class),
            validators: [
                new DuplicateOrderValidator,
                new FeatureFlagValidator,
                new InvalidQuantityValidator,
                new InvalidSymbolValidator,
                new MarketOpenValidator,
                new TradingHoursValidator($app->make(GameRuleProviderContract::class)),
                new SufficientCashValidator,
                new SufficientHoldingsValidator,
                new MaxPositionSizeValidator($app->make(GameRuleProviderContract::class)),
                new MaxDailyTradesValidator($app->make(GameRuleProviderContract::class)),
                new MaxExposureValidator($app->make(GameRuleProviderContract::class)),
                new PremiumFeaturesValidator,
            ]
        ));

        $this->app->singleton(TradingEngineContract::class, fn ($app) => new TradingEngine(
            $app->make(TradingPipeline::class)
        ));
        $this->app->singleton(TradingEngine::class, fn ($app) => $app->make(TradingEngineContract::class));

        // ═════════════════════════════════════════════════════════════════════
        // PORTFOLIO ENGINE SUB-SYSTEM BINDINGS
        // ═════════════════════════════════════════════════════════════════════
        $this->app->register(\App\Portfolio\Providers\PortfolioServiceProvider::class);
    }

    public function boot(): void
    {
        // No boot logic required at this time.
    }
}
