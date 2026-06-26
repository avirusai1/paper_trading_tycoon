<?php

declare(strict_types=1);

namespace App\Portfolio\Exceptions;

use App\Portfolio\Enums\PortfolioValidationReason;

/**
 * Class PortfolioValidationException
 *
 * Thrown when a validator fails inside the portfolio refresh process.
 */
final class PortfolioValidationException extends PortfolioException
{
    public function __construct(
        public readonly PortfolioValidationReason $reason,
        string $message = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message ?: $reason->value,
            $reason->value,
            422,
            $previous
        );
    }
}
