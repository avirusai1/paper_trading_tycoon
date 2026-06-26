<?php

declare(strict_types=1);

namespace App\Trading\Validators;

use App\Enums\OrderSide;
use App\GameEngine\Contracts\GameRuleProviderContract;
use App\Models\Order;
use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\TradingValidatorContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\TradingValidationReason;
use App\Trading\Exceptions\TradingValidationException;

/**
 * Validates that a trade does not exceed the maximum allowed cash/portfolio exposure for a single stock.
 */
final readonly class MaxExposureValidator implements TradingValidatorContract
{
    public function __construct(
        private GameRuleProviderContract $ruleProvider
    ) {}

    public function validate(TradeRequest $request, TradingContext $context): void
    {
        if ($request->side !== OrderSide::Buy) {
            return;
        }

        // 1. Calculate new order value
        $price = $request->limitPricePaise
            ?? $request->stopPricePaise
            ?? $context->quote->ltp->valuePaise;
        $newOrderValue = $request->quantity * $price;

        // 2. Calculate existing holding value for this stock
        $holding = $context->getHolding($context->stock->id);
        $holdingValue = $holding !== null ? ($holding->quantity * $context->quote->ltp->valuePaise) : 0;

        // 3. Calculate current open buy exposure for this stock
        $openBuyExposureForStock = (int) Order::query()
            ->open()
            ->where('user_id', $request->userId)
            ->where('stock_id', $context->stock->id)
            ->where('side', OrderSide::Buy)
            ->get()
            ->sum(function ($order) {
                $price = $order->limit_price_paise ?? $order->stop_price_paise ?? 0;

                return $order->remainingQuantity() * $price;
            });

        $totalExposureForStock = $holdingValue + $openBuyExposureForStock + $newOrderValue;

        // 4. Calculate total portfolio value
        $holdingsValue = 0;
        foreach ($context->holdings as $h) {
            // Use ltp from quote if it's the current stock, or the saved/cached model value for others
            $stockPrice = ($h->stock_id === $context->stock->id)
                ? $context->quote->ltp->valuePaise
                : ($h->current_value_paise / max(1, $h->quantity));
            $holdingsValue += $h->quantity * $stockPrice;
        }
        $totalPortfolioValue = $context->virtualCash() + $holdingsValue;

        // Ensure total portfolio value is not zero to prevent division by zero
        if ($totalPortfolioValue <= 0) {
            $totalPortfolioValue = 100000000; // Default ₹10,00,000 paise
        }

        // Get max exposure percentage (default: 50% of portfolio)
        $maxExposurePercent = $this->ruleProvider->getFloat('game.max_exposure_percent_per_stock', 50.0);
        $maxAllowedExposure = (int) (($totalPortfolioValue * $maxExposurePercent) / 100);

        if ($totalExposureForStock > $maxAllowedExposure) {
            throw new TradingValidationException(
                TradingValidationReason::MaxExposureExceeded,
                "Order exceeds risk exposure limits for stock '{$context->stock->symbol}'. Maximum exposure allowed: ".($maxAllowedExposure / 100).' INR (Current + New: '.($totalExposureForStock / 100).' INR).'
            );
        }
    }
}
