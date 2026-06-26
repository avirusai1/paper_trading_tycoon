<?php

declare(strict_types=1);

namespace App\Trading\Strategies;

use App\Enums\OrderSide;
use App\MarketData\DTOs\StockQuote;
use App\Models\Order;
use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\OrderExecutionStrategyContract;
use App\Trading\DTOs\TradeRequest;

/**
 * Handles Stop-loss and Stop-limit triggers.
 */
final class StopLossStrategy implements OrderExecutionStrategyContract
{
    public function canFillImmediately(TradeRequest $request, TradingContext $context): bool
    {
        $ltp = $context->quote->ltp->valuePaise;
        $stopPrice = $request->stopPricePaise;

        if ($stopPrice === null) {
            return false;
        }

        if ($request->side === OrderSide::Buy) {
            return $ltp >= $stopPrice;
        }

        return $ltp <= $stopPrice;
    }

    public function canFillOpenOrder(Order $order, StockQuote $quote): bool
    {
        $ltp = $quote->ltp->valuePaise;
        $stopPrice = $order->stop_price_paise;

        if ($stopPrice === null) {
            return false;
        }

        $side = $order->side;

        if ($side === OrderSide::Buy) {
            return $ltp >= $stopPrice;
        }

        return $ltp <= $stopPrice;
    }

    public function determineExecutionPrice(Order|TradeRequest $target, StockQuote $quote): int
    {
        // Executes at LTP when triggered
        return $quote->ltp->valuePaise;
    }
}
