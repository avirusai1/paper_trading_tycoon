<?php
declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when a user crosses a level threshold.
 * Listeners: HandleLevelUpForAchievements, HandleLevelUpNotification
 */
final class LevelUp
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $userId,
        public readonly int    $newLevel,
        public readonly string $careerTitle,
        public readonly array  $unlocks,
    ) {}
}
