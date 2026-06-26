<?php

declare(strict_types=1);

namespace App\GameEngine\DTOs;

/**
 * Immutable result of a level-up event.
 *
 * Produced by the XP processor (embedded in XPResult) and also used
 * directly by the pipeline when emitting a LevelUp domain event.
 */
final readonly class LevelResult
{
    public function __construct(
        public readonly int $userId,
        public readonly int $levelBefore,
        public readonly int $levelAfter,
        public readonly string $careerTitleBefore,
        public readonly string $careerTitleAfter,
        /** Coin reward for reaching this level, from the levels table. */
        public readonly int $coinReward,
        /** Feature/content unlocks for the new level (from levels.unlocks JSON). */
        public readonly array $unlocks,
    ) {}

    public function didChangeCareerTitle(): bool
    {
        return $this->careerTitleAfter !== $this->careerTitleBefore;
    }

    public function levelsGained(): int
    {
        return max(0, $this->levelAfter - $this->levelBefore);
    }
}
