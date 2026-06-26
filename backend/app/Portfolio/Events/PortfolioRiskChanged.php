<?php

declare(strict_types=1);

namespace App\Portfolio\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PortfolioRiskChanged
 *
 * Dispatched when a portfolio risk rating shifts significantly.
 */
final class PortfolioRiskChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $oldRiskScore,
        public readonly int $newRiskScore,
        public readonly string $riskLabel
    ) {}
}
