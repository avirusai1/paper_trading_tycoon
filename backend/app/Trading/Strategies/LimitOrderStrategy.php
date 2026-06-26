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
 * Handles Limit orders.
 */
final class LimitOrderStrategy implements OrderExecutionStrategyContract
{
    public function canFillImmediately(TradeRequest $request, TradingContext $context): bool
    {
        $ltp = $context->quote->ltp->valuePaise;
        $limitPrice = $request->limitPricePaise;

        if ($limitPrice === null) {
            return false;
        }

        if ($request->side === OrderSide::Buy) {
            return $ltp <= $limitPrice;
        }

        return $ltp >= $limitPrice;
    }

    public function canFillOpenOrder(Order $order, StockQuote $quote): bool
    {
        $ltp = $quote->ltp->valuePaise;
        $limitPrice = $order->limit_price_paise;

        if ($limitPrice === null) {
            return false;
        }

        $side = $order->side; // Enum OrderSide cast

        if ($side === OrderSide::Buy) {
            return $ltp <= $limitPrice;
        }

        return $ltp >= $limitPrice;
    }

    public function determineExecutionPrice(Order|TradeRequest $target, StockQuote $quote): int
    {
        if ($target instanceof Order) {
            return $target->limit_price_paise ?? $quote->ltp->valuePaise;
        }

        return $target->limitPricePaise ?? $quote->ltp->valuePaise;
    }
}
