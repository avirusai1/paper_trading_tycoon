<?php
declare(strict_types=1);

namespace App\GameEngine\Exceptions;

use App\Exceptions\DomainException;

/**
 * Root exception for the Game Engine subsystem.
 *
 * All Game Engine exceptions extend this class, which itself extends the
 * application's DomainException. The global exception handler maps
 * DomainException subclasses to structured API error responses.
 */
class GameEngineException extends DomainException
{
    public function __construct(
        string $message,
        private readonly string $code,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function errorCode(): string
    {
        return $this->code;
    }
}
