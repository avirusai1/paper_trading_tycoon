<?php

declare(strict_types=1);

namespace App\Trading\Calculators;

/**
 * Handles all financial calculations for trades, portfolios, and P&L.
 */
final class TradeCalculator
{
    /**
     * Calculate total trade value (quantity * price).
     */
    public static function totalValue(int $quantity, int $pricePaise): int
    {
        return (int) bcmul((string) $quantity, (string) $pricePaise, 0);
    }

    /**
     * Calculate brokerage fee (zero in V1).
     */
    public static function brokerage(int $totalValuePaise): int
    {
        return 0;
    }

    /**
     * Calculate taxes (e.g. STT/GST: 0.1% of total value).
     */
    public static function tax(int $totalValuePaise): int
    {
        return (int) bcdiv(bcmul((string) $totalValuePaise, '1', 0), '1000', 0);
    }

    /**
     * Calculate transaction fees (e.g. exchange txn charges: 0.003% of total value).
     */
    public static function transactionFees(int $totalValuePaise): int
    {
        return (int) bcdiv(bcmul((string) $totalValuePaise, '3', 0), '100000', 0);
    }

    /**
     * Calculate realized P&L when selling shares.
     */
    public static function realizedPnl(int $quantity, int $sellPricePaise, int $avgBuyPricePaise): int
    {
        $cost = (int) bcmul((string) $quantity, (string) $avgBuyPricePaise, 0);
        $proceeds = (int) bcmul((string) $quantity, (string) $sellPricePaise, 0);

        return $proceeds - $cost;
    }
}
