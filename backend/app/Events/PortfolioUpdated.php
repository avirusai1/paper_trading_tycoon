<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched after a portfolio snapshot is recorded.
 * Listeners: HandlePortfolioUpdatedForGame, HandlePortfolioUpdatedForLeaderboard
 */
final class PortfolioUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $totalValuePaise,
        public readonly int $cashPaise,
        public readonly int $holdingsCount,
    ) {}
}
