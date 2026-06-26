<?php

declare(strict_types=1);

namespace App\Portfolio\Providers;

use App\Portfolio\Contracts\PortfolioRepositoryContract;
use App\Portfolio\Contracts\SnapshotRepositoryContract;
use App\Portfolio\Contracts\AnalyticsRepositoryContract;
use App\Portfolio\Contracts\PortfolioServiceContract;
use App\Portfolio\Repositories\PortfolioRepository;
use App\Portfolio\Repositories\SnapshotRepository;
use App\Portfolio\Repositories\AnalyticsRepository;
use App\Portfolio\Services\PortfolioService;
use App\Portfolio\Pipelines\RefreshPortfolioPipeline;
use App\Portfolio\Actions\LoadPortfolioContextAction;
use App\Portfolio\Actions\CalculatePortfolioAction;
use App\Portfolio\Actions\CalculateAnalyticsAction;
use App\Portfolio\Actions\CalculateRiskAction;
use App\Portfolio\Actions\GenerateSnapshotAction;
use App\Portfolio\Actions\PublishPortfolioAction;
use App\Portfolio\Validators\NegativeBalanceValidator;
use App\Portfolio\Validators\HoldingConsistencyValidator;
use App\Portfolio\Validators\MarketDataValidator;
use App\Portfolio\Validators\PortfolioIntegrityValidator;
use App\Portfolio\Validators\SnapshotValidator;
use Illuminate\Support\ServiceProvider;

/**
 * Class PortfolioServiceProvider
 *
 * Configures Dependency Injection container bindings for the Portfolio Engine subsystem.
 */
final class PortfolioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. Bind Repositories
        $this->app->singleton(PortfolioRepositoryContract::class, PortfolioRepository::class);
        $this->app->singleton(SnapshotRepositoryContract::class, SnapshotRepository::class);
        $this->app->singleton(AnalyticsRepositoryContract::class, AnalyticsRepository::class);

        // 2. Bind Validators (Transient)
        $this->app->bind(NegativeBalanceValidator::class, NegativeBalanceValidator::class);
        $this->app->bind(HoldingConsistencyValidator::class, HoldingConsistencyValidator::class);
        $this->app->bind(MarketDataValidator::class, MarketDataValidator::class);
        $this->app->bind(PortfolioIntegrityValidator::class, PortfolioIntegrityValidator::class);
        $this->app->bind(SnapshotValidator::class, SnapshotValidator::class);

        // 3. Bind Refresh Pipeline
        $this->app->singleton(RefreshPortfolioPipeline::class, function ($app) {
            return new RefreshPortfolioPipeline(
                loadContextAction: $app->make(LoadPortfolioContextAction::class),
                calculatePortfolioAction: $app->make(CalculatePortfolioAction::class),
                calculateAnalyticsAction: $app->make(CalculateAnalyticsAction::class),
                calculateRiskAction: $app->make(CalculateRiskAction::class),
                generateSnapshotAction: $app->make(GenerateSnapshotAction::class),
                publishPortfolioAction: $app->make(PublishPortfolioAction::class),
                validators: [
                    $app->make(NegativeBalanceValidator::class),
                    $app->make(HoldingConsistencyValidator::class),
                    $app->make(MarketDataValidator::class),
                    $app->make(PortfolioIntegrityValidator::class),
                    $app->make(SnapshotValidator::class),
                ]
            );
        });

        // 4. Bind Portfolio Service Contract
        $this->app->singleton(PortfolioServiceContract::class, PortfolioService::class);
    }
}
