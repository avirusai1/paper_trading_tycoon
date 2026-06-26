<?php

declare(strict_types=1);

namespace App\Portfolio\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PortfolioCalculated
 *
 * Dispatched inside the refresh pipeline when all portfolio valuations are computed.
 */
final class PortfolioCalculated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $netWorthPaise,
        public readonly int $absoluteReturnPaise,
        public readonly float $percentageReturn
    ) {}
}
