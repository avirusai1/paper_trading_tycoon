<?php

declare(strict_types=1);

namespace App\Portfolio\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PortfolioMilestoneReached
 *
 * Dispatched when a portfolio net worth crosses a major milestone (e.g. ₹20,00,000, etc.).
 */
final class PortfolioMilestoneReached
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $milestoneValuePaise,
        public readonly string $milestoneName
    ) {}
}
