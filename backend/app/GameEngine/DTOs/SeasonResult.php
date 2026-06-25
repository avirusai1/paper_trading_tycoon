<?php
declare(strict_types=1);

namespace App\GameEngine\DTOs;

/**
 * Immutable result of a season enrollment or reward distribution operation.
 */
final readonly class SeasonResult
{
    public function __construct(
        public readonly int    $userId,
        public readonly int    $seasonId,
        public readonly string $seasonName,
        /** True when this call created the enrollment (vs. found existing). */
        public readonly bool   $enrolled,
        /** Coins granted as season-end reward, 0 during enrollment. */
        public readonly int    $coinsGranted,
        /** XP granted as season-end reward, 0 during enrollment. */
        public readonly int    $xpGranted,
    ) {}
}
