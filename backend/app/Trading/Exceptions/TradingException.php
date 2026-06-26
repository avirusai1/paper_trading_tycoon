<?php

declare(strict_types=1);

namespace App\Trading\Exceptions;

use App\Exceptions\DomainException;

/**
 * Root exception for the Trading Engine subsystem.
 */
class TradingException extends DomainException
{
    public function __construct(
        string $message,
        private readonly string $errorCode,
        private readonly int $status = 422,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function httpStatus(): int
    {
        return $this->status;
    }
}
