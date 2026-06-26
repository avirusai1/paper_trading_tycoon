<?php

declare(strict_types=1);

namespace App\Portfolio\Calculators;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\ValueObjects\PortfolioReturn;
use App\Portfolio\ValueObjects\ProfitLoss;

/**
 * Class ReturnCalculator
 *
 * Computes portfolio return metrics.
 */
final class ReturnCalculator
{
    private const DEFAULT_STARTING_CASH_PAISE = 100000000; // ₹10,00,000 = 100,000,000 paise

    /**
     * Calculates absolute and percentage return since user creation (or total deposits).
     *
     * @param PortfolioContext $context
     * @param int $currentPortfolioValue
     * @return ProfitLoss
     */
    public function absoluteReturn(PortfolioContext $context, int $currentPortfolioValue): ProfitLoss
    {
        $startingCash = $context->wallet->total_deposited_paise > 0 
            ? $context->wallet->total_deposited_paise 
            : self::DEFAULT_STARTING_CASH_PAISE;

        $absoluteReturn = $currentPortfolioValue - $startingCash;
        $percentageReturn = $startingCash > 0 ? (float) (($absoluteReturn * 100) / $startingCash) : 0.0;

        return new ProfitLoss($absoluteReturn, $percentageReturn);
    }

    /**
     * Calculates percentage return metrics.
     *
     * @param PortfolioContext $context
     * @param int $currentPortfolioValue
     * @return PortfolioReturn
     */
    public function percentageReturn(PortfolioContext $context, int $currentPortfolioValue): PortfolioReturn
    {
        $startingCash = $context->wallet->total_deposited_paise > 0 
            ? $context->wallet->total_deposited_paise 
            : self::DEFAULT_STARTING_CASH_PAISE;

        $absoluteReturn = $currentPortfolioValue - $startingCash;
        $percentageReturn = $startingCash > 0 ? (float) (($absoluteReturn * 100) / $startingCash) : 0.0;

        return new PortfolioReturn($absoluteReturn, $percentageReturn);
    }

    /**
     * Calculates today's profit/loss based on holding LTP shifts from previous close.
     *
     * @param PortfolioContext $context
     * @param int $currentPortfolioValue
     * @return ProfitLoss
     */
    public function todayProfitLoss(PortfolioContext $context, int $currentPortfolioValue): ProfitLoss
    {
        $todayPnl = 0;

        foreach ($context->holdings as $holding) {
            if ($holding->quantity > 0) {
                $quote = $context->getQuote($holding->symbol);
                if ($quote !== null) {
                    $todayPnl += $holding->quantity * $quote->change->valuePaise;
                }
            }
        }

        $yesterdayValue = $currentPortfolioValue - $todayPnl;
        $todayReturnPercent = $yesterdayValue > 0 ? (float) (($todayPnl * 100) / $yesterdayValue) : 0.0;

        return new ProfitLoss($todayPnl, $todayReturnPercent);
    }

    /**
     * Calculates compounded annual return (CAGR) based on total return and holding period.
     *
     * @param float $totalReturnPercent
     * @param float $years
     * @return float
     */
    public function compoundedReturn(float $totalReturnPercent, float $years): float
    {
        if ($years <= 0) {
            return $totalReturnPercent;
        }

        $decimalReturn = $totalReturnPercent / 100;
        $base = 1.0 + $decimalReturn;

        if ($base <= 0) {
            return -100.0; // Total loss if base is zero or negative
        }

        $cagr = (pow($base, 1.0 / $years) - 1.0) * 100.0;

        return (float) $cagr;
    }
}
