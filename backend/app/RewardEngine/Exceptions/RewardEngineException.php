<?php

declare(strict_types=1);

namespace App\RewardEngine\Exceptions;

use App\Exceptions\DomainException;

/**
 * Root exception for the Reward Engine subsystem.
 *
 * All Reward Engine exceptions extend this class. The global exception handler
 * maps DomainException subclasses to structured JSON API error responses with
 * the errorCode() value as the machine-readable discriminator.
 */
class RewardEngineException extends DomainException
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
