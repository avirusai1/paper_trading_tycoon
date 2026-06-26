<?php

declare(strict_types=1);

namespace App\Trading\Validators;

use App\Enums\OrderSide;
use App\GameEngine\Contracts\GameRuleProviderContract;
use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\TradingValidatorContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\TradingValidationReason;
use App\Trading\Exceptions\TradingValidationException;

/**
 * Validates that adding a new stock position does not exceed the maximum allowed holdings per user.
 */
final readonly class MaxPositionSizeValidator implements TradingValidatorContract
{
    public function __construct(
        private GameRuleProviderContract $ruleProvider
    ) {}

    public function validate(TradeRequest $request, TradingContext $context): void
    {
        if ($request->side !== OrderSide::Buy) {
            return;
        }

        $holding = $context->getHolding($context->stock->id);
        $hasActiveHolding = $holding !== null && $holding->quantity > 0;

        // If the user already has an active position in this stock, it won't increase the number of unique positions
        if ($hasActiveHolding) {
            return;
        }

        // Count unique active holdings
        $activeHoldingsCount = 0;
        foreach ($context->holdings as $h) {
            if ($h->quantity > 0) {
                $activeHoldingsCount++;
            }
        }

        $maxHoldings = $this->ruleProvider->getInt('game.max_holdings_per_user', 20);

        if ($activeHoldingsCount >= $maxHoldings) {
            throw new TradingValidationException(
                TradingValidationReason::MaxPositionsExceeded,
                "Cannot buy new stock. You have reached the maximum allowed unique positions of {$maxHoldings}."
            );
        }
    }
}
