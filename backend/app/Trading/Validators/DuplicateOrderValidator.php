<?php

declare(strict_types=1);

namespace App\Trading\Validators;

use App\Models\Order;
use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\TradingValidatorContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\TradingValidationReason;
use App\Trading\Exceptions\TradingValidationException;

/**
 * Validates that an order with the same idempotency key has not been processed.
 */
final class DuplicateOrderValidator implements TradingValidatorContract
{
    public function validate(TradeRequest $request, TradingContext $context): void
    {
        $exists = Order::query()
            ->where('idempotency_key', $request->idempotencyKey)
            ->exists();

        if ($exists) {
            throw new TradingValidationException(
                TradingValidationReason::DuplicateOrder,
                "An order with idempotency key '{$request->idempotencyKey}' already exists."
            );
        }
    }
}
