<?php

declare(strict_types=1);

namespace App\Listeners\Game;

use App\Events\ChallengeCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Paper Trading Tycoon — HandleChallengeCompletedForRewards
 *
 * Queued listener for ChallengeCompleted.
 * Must be idempotent — safe to re-run on queue retry.
 * Implementation per milestone schedule.
 */
final class HandleChallengeCompletedForRewards implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ChallengeCompleted $event): void
    {
        // Implementation per milestone.
    }
}
