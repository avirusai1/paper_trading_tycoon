<?php

declare(strict_types=1);

namespace App\Trading\Validators;

use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\TradingValidatorContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\TradingValidationReason;
use App\Trading\Exceptions\TradingValidationException;

/**
 * Validates that the requested trade quantity is positive and within acceptable bounds.
 */
final class InvalidQuantityValidator implements TradingValidatorContract
{
    private const MAX_QUANTITY = 1000000; // Limit to 1,000,000 shares per order to prevent abuse

    public function validate(TradeRequest $request, TradingContext $context): void
    {
        if ($request->quantity <= 0) {
            throw new TradingValidationException(
                TradingValidationReason::InvalidQuantity,
                "Order quantity must be greater than zero. Got: {$request->quantity}."
            );
        }

        if ($request->quantity > self::MAX_QUANTITY) {
            throw new TradingValidationException(
                TradingValidationReason::InvalidQuantity,
                'Order quantity cannot exceed '.number_format(self::MAX_QUANTITY).' shares.'
            );
        }
    }
}
