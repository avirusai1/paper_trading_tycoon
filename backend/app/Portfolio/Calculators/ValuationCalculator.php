<?php

declare(strict_types=1);

namespace App\Portfolio\Calculators;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\ValueObjects\CashValue;
use App\Portfolio\ValueObjects\HoldingValue;
use App\Portfolio\ValueObjects\PortfolioValue;

/**
 * Class ValuationCalculator
 *
 * Computes portfolio component and aggregate valuations.
 */
final class ValuationCalculator
{
    /**
     * Calculates the cash value from user wallet.
     *
     * @param PortfolioContext $context
     * @return CashValue
     */
    public function cashValue(PortfolioContext $context): CashValue
    {
        return new CashValue($context->cashPaise());
    }

    /**
     * Calculates the current market value of all active holdings.
     *
     * @param PortfolioContext $context
     * @return HoldingValue
     */
    public function holdingValue(PortfolioContext $context): HoldingValue
    {
        $totalPaise = 0;

        foreach ($context->holdings as $holding) {
            if ($holding->quantity > 0) {
                $quote = $context->getQuote($holding->symbol);
                if ($quote !== null) {
                    $totalPaise += $holding->quantity * $quote->ltp->valuePaise;
                } else {
                    $totalPaise += $holding->current_value_paise;
                }
            }
        }

        return new HoldingValue($totalPaise);
    }

    /**
     * Calculates total portfolio net worth (cash + holdings).
     *
     * @param PortfolioContext $context
     * @return PortfolioValue
     */
    public function netWorth(PortfolioContext $context): PortfolioValue
    {
        $cash = $this->cashValue($context)->valuePaise;
        $holdings = $this->holdingValue($context)->valuePaise;

        return new PortfolioValue($cash + $holdings);
    }
}
