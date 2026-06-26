<?php

declare(strict_types=1);

namespace App\Trading\Strategies;

use App\MarketData\DTOs\StockQuote;
use App\Models\Order;
use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\OrderExecutionStrategyContract;
use App\Trading\DTOs\TradeRequest;

/**
 * Handles Bracket orders. Primary order executes at market LTP immediately.
 */
final class BracketOrderStrategy implements OrderExecutionStrategyContract
{
    public function canFillImmediately(TradeRequest $request, TradingContext $context): bool
    {
        return true; // Primary leg executes immediately at market LTP
    }

    public function canFillOpenOrder(Order $order, StockQuote $quote): bool
    {
        return false; // The main leg fills immediately; children are separate limit/stop orders
    }

    public function determineExecutionPrice(Order|TradeRequest $target, StockQuote $quote): int
    {
        return $quote->ltp->valuePaise;
    }
}
