<?php

declare(strict_types=1);

namespace App\GameEngine\DTOs;

/**
 * Immutable result of a career title evaluation pass.
 */
final readonly class CareerResult
{
    public function __construct(
        public readonly int $userId,
        public readonly int $currentLevel,
        public readonly string $titleBefore,
        public readonly string $titleAfter,
        /** True if the career title changed as a result of the evaluation. */
        public readonly bool $titleChanged,
    ) {}
}
