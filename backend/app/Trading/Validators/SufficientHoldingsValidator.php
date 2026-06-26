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
 * Validates that the user has sufficient holding quantity to place a sell order, accounting for open sell orders.
 */
final class SufficientHoldingsValidator implements TradingValidatorContract
{
    public function validate(TradeRequest $request, TradingContext $context): void
    {
        if ($request->side !== OrderSide::Sell) {
            return;
        }

        $holding = $context->getHolding($context->stock->id);
        $ownedQuantity = $holding !== null ? $holding->quantity : 0;
        $openSellQuantity = $context->getOpenSellQuantity($context->stock->id);
        $availableQuantity = $ownedQuantity - $openSellQuantity;

        if ($request->quantity > $availableQuantity) {
            throw new TradingValidationException(
                TradingValidationReason::InsufficientHoldings,
                "Insufficient stock holdings. Available: {$availableQuantity} shares (Owned: {$ownedQuantity}, Committed in open orders: {$openSellQuantity}). Requested: {$request->quantity} shares."
            );
        }
    }
}
