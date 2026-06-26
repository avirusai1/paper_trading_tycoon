<?php

declare(strict_types=1);

namespace App\Portfolio\Validators;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\Contracts\PortfolioValidatorContract;
use App\Portfolio\Enums\PortfolioValidationReason;
use App\Portfolio\Exceptions\PortfolioValidationException;

/**
 * Class NegativeBalanceValidator
 *
 * Validates that user's virtual cash balance is not negative.
 */
final class NegativeBalanceValidator implements PortfolioValidatorContract
{
    public function validate(PortfolioContext $context): void
    {
        if ($context->wallet->virtual_cash_paise < 0) {
            throw new PortfolioValidationException(
                PortfolioValidationReason::NegativeWalletBalance,
                "User cash balance cannot be negative: {$context->wallet->virtual_cash_paise}"
            );
        }
    }
}
