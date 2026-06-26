<?php

declare(strict_types=1);

namespace App\Portfolio\Exceptions;

use App\Exceptions\DomainException;

/**
 * Class PortfolioException
 *
 * Root exception for all errors within the Portfolio Engine.
 */
class PortfolioException extends DomainException
{
    public function __construct(
        string $message,
        private readonly string $errorCode,
        private readonly int $status = 422,
        ?\Throwable $previous = null
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
