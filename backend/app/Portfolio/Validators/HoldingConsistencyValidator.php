<?php

declare(strict_types=1);

namespace App\Portfolio\Validators;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\Contracts\PortfolioValidatorContract;
use App\Portfolio\Enums\PortfolioValidationReason;
use App\Portfolio\Exceptions\PortfolioValidationException;

/**
 * Class HoldingConsistencyValidator
 *
 * Validates that holding quantities and cost bases are non-negative.
 */
final class HoldingConsistencyValidator implements PortfolioValidatorContract
{
    public function validate(PortfolioContext $context): void
    {
        foreach ($context->holdings as $holding) {
            if ($holding->quantity < 0) {
                throw new PortfolioValidationException(
                    PortfolioValidationReason::HoldingInconsistency,
                    "Holding quantity for symbol {$holding->symbol} cannot be negative: {$holding->quantity}"
                );
            }

            if ($holding->average_buy_price_paise < 0) {
                throw new PortfolioValidationException(
                    PortfolioValidationReason::HoldingInconsistency,
                    "Holding average buy price for symbol {$holding->symbol} cannot be negative: {$holding->average_buy_price_paise}"
                );
            }
        }
    }
}
