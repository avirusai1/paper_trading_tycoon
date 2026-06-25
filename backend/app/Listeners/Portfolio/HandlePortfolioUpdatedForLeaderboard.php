<?php

declare(strict_types=1);

namespace App\Listeners\Portfolio;

use App\Events\PortfolioUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Paper Trading Tycoon — HandlePortfolioUpdatedForLeaderboard
 *
 * Queued listener for PortfolioUpdated.
 * Must be idempotent — safe to re-run on queue retry.
 * Implementation per milestone schedule.
 */
final class HandlePortfolioUpdatedForLeaderboard implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PortfolioUpdated $event): void
    {
        // Implementation per milestone.
    }
}
