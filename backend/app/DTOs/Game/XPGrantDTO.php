<?php

declare(strict_types=1);

namespace App\DTOs\Game;

/**
 * Paper Trading Tycoon — XP Grant Data Transfer Object
 *
 * Carries XP grant parameters from event listeners to the XPEngine.
 */
final readonly class XPGrantDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $source,
        public readonly string $sourceId,
        public readonly int $amount,
    ) {}
}
