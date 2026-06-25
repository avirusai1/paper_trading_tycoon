<?php

declare(strict_types=1);

namespace App\Listeners\Game;

use App\Events\XPGranted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Paper Trading Tycoon — HandleXPGrantedForChallenges
 *
 * Queued listener for XPGranted.
 * Must be idempotent — safe to re-run on queue retry.
 * Implementation per milestone schedule.
 */
final class HandleXPGrantedForChallenges implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(XPGranted $event): void
    {
        // Implementation per milestone.
    }
}
