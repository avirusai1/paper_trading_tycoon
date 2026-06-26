<?php

declare(strict_types=1);

namespace App\GameEngine\DTOs;

use App\Enums\LeagueTier;

/**
 * Immutable result of a league standing update or season-end evaluation.
 */
final readonly class LeagueResult
{
    public function __construct(
        public readonly int $userId,
        public readonly int $seasonId,
        public readonly LeagueTier $tier,
        public readonly int $rankPosition,
        public readonly int $portfolioValuePaise,
        public readonly float $returnPercent,
        /** Filled at season end: 'promoted' | 'demoted' | 'stayed'. */
        public readonly ?string $seasonResult = null,
    ) {}
}
