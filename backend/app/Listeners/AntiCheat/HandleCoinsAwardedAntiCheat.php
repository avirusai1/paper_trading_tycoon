<?php

declare(strict_types=1);

namespace App\Listeners\AntiCheat;

use App\Events\CoinsAwarded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Paper Trading Tycoon — HandleCoinsAwardedAntiCheat
 *
 * Queued listener for CoinsAwarded.
 * Must be idempotent — safe to re-run on queue retry.
 * Implementation per milestone schedule.
 */
final class HandleCoinsAwardedAntiCheat implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CoinsAwarded $event): void
    {
        // Implementation per milestone.
    }
}
