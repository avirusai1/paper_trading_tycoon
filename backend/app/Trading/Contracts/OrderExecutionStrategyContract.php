<?php

declare(strict_types=1);

namespace App\Trading\Contracts;

use App\MarketData\DTOs\StockQuote;
use App\Models\Order;
use App\Trading\Contexts\TradingContext;
use App\Trading\DTOs\TradeRequest;

/**
 * Paper Trading Tycoon — Order Execution Strategy Contract
 */
interface OrderExecutionStrategyContract
{
    /**
     * Determine if a newly submitted trade request can be filled immediately at the current quote.
     */
    public function canFillImmediately(TradeRequest $request, TradingContext $context): bool;

    /**
     * Determine if an existing open order can be filled given a fresh stock price quote.
     */
    public function canFillOpenOrder(Order $order, StockQuote $quote): bool;

    /**
     * Determine the exact execution price (in paise) for a fill.
     */
    public function determineExecutionPrice(Order|TradeRequest $target, StockQuote $quote): int;
}
