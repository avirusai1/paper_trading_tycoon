<?php

declare(strict_types=1);

namespace App\Trading\Exceptions;

use App\Trading\Enums\TradingValidationReason;

/**
 * Thrown when a trade request fails a validator in the pipeline validation chain.
 */
final class TradingValidationException extends TradingException
{
    public function __construct(
        public readonly TradingValidationReason $reason,
        string $message = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            $message ?: $reason->value,
            $reason->value,
            422,
            $previous,
        );
    }
}
