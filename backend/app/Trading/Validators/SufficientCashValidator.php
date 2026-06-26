<?php

declare(strict_types=1);

namespace App\Trading\Validators;

use App\Enums\OrderSide;
use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\TradingValidatorContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\TradingValidationReason;
use App\Trading\Exceptions\TradingValidationException;

/**
 * Validates that the user has sufficient cash to place a buy order, accounting for open order exposure.
 */
final class SufficientCashValidator implements TradingValidatorContract
{
    public function validate(TradeRequest $request, TradingContext $context): void
    {
        if ($request->side !== OrderSide::Buy) {
            return;
        }

        // Determine price to check based on order type
        $price = $request->limitPricePaise
            ?? $request->stopPricePaise
            ?? $context->quote->ltp->valuePaise;

        if ($price <= 0) {
            throw new TradingValidationException(
                TradingValidationReason::InvalidPrice,
                "Determined price for order validation must be positive. Got: {$price} paise."
            );
        }

        $requiredCash = $request->quantity * $price;
        $availableCash = $context->virtualCash() - $context->openOrderExposurePaise;

        if ($requiredCash > $availableCash) {
            throw new TradingValidationException(
                TradingValidationReason::InsufficientFunds,
                'Insufficient funds. Required: '.($requiredCash / 100).' INR, Available: '.($availableCash / 100).' INR (accounting for open orders).'
            );
        }
    }
}
