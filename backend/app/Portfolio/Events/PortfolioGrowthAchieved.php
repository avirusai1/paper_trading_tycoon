<?php

declare(strict_types=1);

namespace App\Portfolio\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PortfolioGrowthAchieved
 *
 * Dispatched when a portfolio records significant positive growth (e.g., crossing a return threshold).
 */
final class PortfolioGrowthAchieved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly float $growthPercent,
        public readonly int $netWorthPaise
    ) {}
}
