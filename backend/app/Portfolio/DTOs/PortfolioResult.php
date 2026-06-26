<?php

declare(strict_types=1);

namespace App\Portfolio\DTOs;

use App\Portfolio\ValueObjects\PortfolioValue;
use App\Portfolio\ValueObjects\CashValue;
use App\Portfolio\ValueObjects\HoldingValue;
use App\Portfolio\ValueObjects\PortfolioReturn;
use App\Portfolio\ValueObjects\ProfitLoss;

/**
 * Class PortfolioResult
 *
 * Immutable master result carrying calculated values for the entire portfolio subsystem.
 */
final readonly class PortfolioResult
{
    public function __construct(
        public int $userId,
        public PortfolioValue $netWorth,
        public CashValue $cashValue,
        public HoldingValue $holdingValue,
        public ProfitLoss $absoluteReturn,
        public PortfolioReturn $percentageReturn,
        public ProfitLoss $todayProfitLoss,
        public PortfolioAnalyticsResult $analytics,
        public PortfolioRiskResult $risk,
        public float $elapsedTimeMs
    ) {}
}
