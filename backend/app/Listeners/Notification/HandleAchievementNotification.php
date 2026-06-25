<?php

declare(strict_types=1);

namespace App\Listeners\Notification;

use App\Events\AchievementUnlocked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Paper Trading Tycoon — HandleAchievementNotification
 *
 * Queued listener for AchievementUnlocked.
 * Must be idempotent — safe to re-run on queue retry.
 * Implementation per milestone schedule.
 */
final class HandleAchievementNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AchievementUnlocked $event): void
    {
        // Implementation per milestone.
    }
}
