<?php

declare(strict_types=1);

namespace App\Trading\Validators;

use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\TradingValidatorContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\TradingValidationReason;
use App\Trading\Exceptions\TradingValidationException;

/**
 * Validates that the stock exchange is currently open.
 */
final class MarketOpenValidator implements TradingValidatorContract
{
    public function validate(TradeRequest $request, TradingContext $context): void
    {
        if (! $context->marketStatus->isOpen) {
            throw new TradingValidationException(
                TradingValidationReason::MarketClosed,
                "Market is currently closed for exchange '{$context->stock->exchange}'."
            );
        }
    }
}
