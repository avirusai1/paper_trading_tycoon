<?php

declare(strict_types=1);

namespace App\Trading\Validators;

use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\TradingValidatorContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\TradingValidationReason;
use App\Trading\Exceptions\TradingValidationException;

/**
 * Validates that the target stock is active and allowed for trading.
 */
final class InvalidSymbolValidator implements TradingValidatorContract
{
    public function validate(TradeRequest $request, TradingContext $context): void
    {
        if (! $context->stock->is_active) {
            throw new TradingValidationException(
                TradingValidationReason::InvalidSymbol,
                "Stock '{$context->stock->symbol}' is currently inactive."
            );
        }

        if (! $context->stock->is_tradeable) {
            throw new TradingValidationException(
                TradingValidationReason::InvalidSymbol,
                "Stock '{$context->stock->symbol}' is not marked as tradeable."
            );
        }
    }
}
