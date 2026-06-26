<?php

declare(strict_types=1);

namespace App\Trading\Strategies;

use App\MarketData\DTOs\StockQuote;
use App\Models\Order;
use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\OrderExecutionStrategyContract;
use App\Trading\DTOs\TradeRequest;

/**
 * Handles Market orders which fill immediately at the current Last Traded Price (LTP).
 */
final class MarketOrderStrategy implements OrderExecutionStrategyContract
{
    public function canFillImmediately(TradeRequest $request, TradingContext $context): bool
    {
        return true;
    }

    public function canFillOpenOrder(Order $order, StockQuote $quote): bool
    {
        return false; // Market orders are never left open in the book
    }

    public function determineExecutionPrice(Order|TradeRequest $target, StockQuote $quote): int
    {
        return $quote->ltp->valuePaise;
    }
}
