<?php

declare(strict_types=1);

namespace App\Trading\Validators;

use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\TradingValidatorContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\OrderType;
use App\Trading\Enums\TradingValidationReason;
use App\Trading\Exceptions\TradingValidationException;

/**
 * Validates that premium-only features (such as Bracket orders) are restricted to premium subscribers.
 */
final class PremiumFeaturesValidator implements TradingValidatorContract
{
    public function validate(TradeRequest $request, TradingContext $context): void
    {
        // Gating advanced order types for non-premium users
        if ($request->type === OrderType::Bracket && ! $context->isPremium) {
            throw new TradingValidationException(
                TradingValidationReason::PremiumOnly,
                'Bracket orders are a premium-only feature. Please upgrade your subscription to access them.'
            );
        }
    }
}
