<?php

declare(strict_types=1);

namespace App\Portfolio\Actions;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\Calculators\RiskCalculator;
use App\Portfolio\Contracts\SnapshotRepositoryContract;
use App\Portfolio\DTOs\PortfolioRiskResult;

/**
 * Class CalculateRiskAction
 *
 * Runs the risk assessment engine to compute drawdown, volatility, and safety metrics.
 */
final readonly class CalculateRiskAction
{
    public function __construct(
        private RiskCalculator $riskCalculator,
        private SnapshotRepositoryContract $snapshotRepository
    ) {}

    /**
     * Executes risk evaluation.
     *
     * @param PortfolioContext $context
     * @param int $netWorthPaise
     * @param int $holdingValuePaise
     * @param float $winRate
     * @return PortfolioRiskResult
     */
    public function execute(
        PortfolioContext $context,
        int $netWorthPaise,
        int $holdingValuePaise,
        float $winRate
    ): PortfolioRiskResult {
        // Fetch up to 365 days of daily/hourly snapshots for risk calculation
        $historicalSnapshots = $this->snapshotRepository->getHistory(
            $context->userId(),
            ['daily', 'hourly', 'manual'],
            now()->subYear()
        );

        return $this->riskCalculator->calculate(
            $context,
            $netWorthPaise,
            $holdingValuePaise,
            $winRate,
            $historicalSnapshots
        );
    }
}
