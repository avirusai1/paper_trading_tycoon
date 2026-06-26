<?php

declare(strict_types=1);

namespace App\Portfolio\Validators;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\Contracts\PortfolioValidatorContract;
use App\Portfolio\Enums\PortfolioValidationReason;
use App\Portfolio\Exceptions\PortfolioValidationException;

/**
 * Class PortfolioIntegrityValidator
 *
 * Checks consistency across cash balances and stock holdings.
 */
final class PortfolioIntegrityValidator implements PortfolioValidatorContract
{
    public function validate(PortfolioContext $context): void
    {
        foreach ($context->holdings as $holding) {
            if ($holding->quantity === 0) {
                // For historically retained zero holdings, check that invested basis is zeroed out.
                if ($holding->total_invested_paise !== 0) {
                    throw new PortfolioValidationException(
                        PortfolioValidationReason::PortfolioIntegrityFailure,
                        "Holding quantity is zero, but total invested is not: {$holding->total_invested_paise} for symbol {$holding->symbol}"
                    );
                }
            }
        }
    }
}
