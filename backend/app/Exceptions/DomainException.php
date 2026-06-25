<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Paper Trading Tycoon — Domain Exception Base
 *
 * All business-rule violations throw a subclass of DomainException.
 * The global exception handler maps these to structured API error responses.
 *
 * Subclasses should carry a [code] that maps to a machine-readable error string
 * so the Flutter client can display contextual messages.
 *
 * Example subclasses:
 *   - InsufficientFundsException
 *   - MarketClosedException
 *   - DuplicateTradeException
 *   - ReferralFraudException
 */
abstract class DomainException extends RuntimeException
{
    /**
     * Machine-readable error code for client error handling.
     * e.g. 'insufficient_funds', 'market_closed', 'duplicate_trade'.
     */
    abstract public function errorCode(): string;

    /**
     * HTTP status code this exception maps to (default: 422 Unprocessable Entity).
     * Override in subclasses to return 409 (conflict) or other appropriate codes.
     */
    public function httpStatus(): int
    {
        return 422;
    }
}
