<?php

declare(strict_types=1);

namespace App\GameEngine\DTOs;

/**
 * Immutable result of a single XP grant operation.
 *
 * Returned by XPProcessorContract::grant(). The pipeline accumulates
 * XPResult instances into the parent GameResult.
 */
final readonly class XPResult
{
    public function __construct(
        /** User who received the XP. */
        public readonly int $userId,
        /** XP awarded in this grant (after multipliers, capped if applicable). */
        public readonly int $amountGranted,
        /** Total XP before this grant. */
        public readonly int $xpBefore,
        /** Total XP after this grant. */
        public readonly int $xpAfter,
        /** Level before this grant. */
        public readonly int $levelBefore,
        /** Level after this grant (equal to levelBefore if no level-up occurred). */
        public readonly int $levelAfter,
        /** True if levelAfter > levelBefore. */
        public readonly bool $didLevelUp,
        /** XP source enum value (string backing value). */
        public readonly string $source,
        /** Idempotency key for this grant. */
        public readonly string $sourceId,
        /** True if the daily cap was hit and the grant was reduced or skipped. */
        public readonly bool $wasCapApplied,
    ) {}

    public function levelsGained(): int
    {
        return max(0, $this->levelAfter - $this->levelBefore);
    }
}
