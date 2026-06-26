<?php

declare(strict_types=1);

namespace App\Portfolio\Actions;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\Calculators\ValuationCalculator;
use App\Portfolio\Calculators\ReturnCalculator;
use App\Portfolio\ValueObjects\PortfolioValue;
use App\Portfolio\ValueObjects\CashValue;
use App\Portfolio\ValueObjects\HoldingValue;
use App\Portfolio\ValueObjects\PortfolioReturn;
use App\Portfolio\ValueObjects\ProfitLoss;

/**
 * Class CalculatePortfolioAction
 *
 * Coordinates core valuation and return calculations for the portfolio context.
 */
final readonly class CalculatePortfolioAction
{
    public function __construct(
        private ValuationCalculator $valuationCalculator,
        private ReturnCalculator $returnCalculator
    ) {}

    /**
     * Executes the calculations and returns the results.
     *
     * @param PortfolioContext $context
     * @return array{
     *     netWorth: PortfolioValue,
     *     cashValue: CashValue,
     *     holdingValue: HoldingValue,
     *     absoluteReturn: ProfitLoss,
     *     percentageReturn: PortfolioReturn,
     *     todayProfitLoss: ProfitLoss
     * }
     */
    public function execute(PortfolioContext $context): array
    {
        $netWorth = $this->valuationCalculator->netWorth($context);
        $cashValue = $this->valuationCalculator->cashValue($context);
        $holdingValue = $this->valuationCalculator->holdingValue($context);

        $absoluteReturn = $this->returnCalculator->absoluteReturn($context, $netWorth->valuePaise);
        $percentageReturn = $this->returnCalculator->percentageReturn($context, $netWorth->valuePaise);
        $todayProfitLoss = $this->returnCalculator->todayProfitLoss($context, $netWorth->valuePaise);

        return [
            'netWorth' => $netWorth,
            'cashValue' => $cashValue,
            'holdingValue' => $holdingValue,
            'absoluteReturn' => $absoluteReturn,
            'percentageReturn' => $percentageReturn,
            'todayProfitLoss' => $todayProfitLoss,
        ];
    }
}
