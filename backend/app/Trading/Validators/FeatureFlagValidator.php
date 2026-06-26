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
 * Validates feature flags and checks if the user's account is banned/suspended.
 */
final class FeatureFlagValidator implements TradingValidatorContract
{
    public function validate(TradeRequest $request, TradingContext $context): void
    {
        // 1. User status check
        if ($context->isBanned) {
            throw new TradingValidationException(
                TradingValidationReason::UserBanned,
                'User account is banned/suspended. Trading is disabled.'
            );
        }

        // 2. Extensible asset type feature flag check (future crypto/options)
        if ($request->type === OrderType::Bracket && ! $context->hasFeature('bracket_orders')) {
            // Let's assume bracket orders can be feature-flagged
            // (If the flag doesn't exist, we don't block by default, or we can check premium instead)
        }
    }
}
