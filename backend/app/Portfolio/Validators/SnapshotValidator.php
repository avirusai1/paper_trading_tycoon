<?php

declare(strict_types=1);

namespace App\Portfolio\Validators;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\Contracts\PortfolioValidatorContract;
use App\Portfolio\Enums\PortfolioValidationReason;
use App\Portfolio\Exceptions\PortfolioValidationException;

/**
 * Class SnapshotValidator
 *
 * Verifies that the portfolio state context is safe for snapshot creation.
 */
final class SnapshotValidator implements PortfolioValidatorContract
{
    public function validate(PortfolioContext $context): void
    {
        if ($context->user === null || $context->wallet === null) {
            throw new PortfolioValidationException(
                PortfolioValidationReason::InvalidSnapshotData,
                'User or wallet record is missing from PortfolioContext - snapshot aborted.'
            );
        }
    }
}
