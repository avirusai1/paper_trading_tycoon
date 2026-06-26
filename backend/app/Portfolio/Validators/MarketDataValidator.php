<?php

declare(strict_types=1);

namespace App\Portfolio\Validators;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\Contracts\PortfolioValidatorContract;
use App\Portfolio\Enums\PortfolioValidationReason;
use App\Portfolio\Exceptions\PortfolioValidationException;

/**
 * Class MarketDataValidator
 *
 * Validates that quotes exist for all active holdings in the context.
 */
final class MarketDataValidator implements PortfolioValidatorContract
{
    public function validate(PortfolioContext $context): void
    {
        foreach ($context->holdings as $holding) {
            if ($holding->quantity > 0) {
                $quote = $context->getQuote($holding->symbol);

                if ($quote === null) {
                    throw new PortfolioValidationException(
                        PortfolioValidationReason::MarketDataStale,
                        "Missing quote for active holding symbol: {$holding->symbol}"
                    );
                }

                if ($quote->ltp->valuePaise <= 0) {
                    throw new PortfolioValidationException(
                        PortfolioValidationReason::MarketDataStale,
                        "Invalid LTP ({$quote->ltp->valuePaise}) for active holding symbol: {$holding->symbol}"
                    );
                }
            }
        }
    }
}
