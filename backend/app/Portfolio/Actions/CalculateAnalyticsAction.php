<?php

declare(strict_types=1);

namespace App\Portfolio\Actions;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\Calculators\AnalyticsCalculator;
use App\Portfolio\Contracts\AnalyticsRepositoryContract;
use App\Portfolio\DTOs\PortfolioAnalyticsResult;

/**
 * Class CalculateAnalyticsAction
 *
 * Runs the analytics engine to evaluate trade statistics and diversification allocations.
 */
final readonly class CalculateAnalyticsAction
{
    public function __construct(
        private AnalyticsCalculator $analyticsCalculator,
        private AnalyticsRepositoryContract $analyticsRepository
    ) {}

    /**
     * Executes analytics calculations and persists/caches the result.
     *
     * @param PortfolioContext $context
     * @param int $netWorthPaise
     * @param int $holdingValuePaise
     * @return PortfolioAnalyticsResult
     */
    public function execute(
        PortfolioContext $context,
        int $netWorthPaise,
        int $holdingValuePaise
    ): PortfolioAnalyticsResult {
        $result = $this->analyticsCalculator->calculate(
            $context,
            $netWorthPaise,
            $holdingValuePaise
        );

        // Cache the analytics results
        $this->analyticsRepository->save($context->userId(), $result);

        return $result;
    }
}
