<?php
declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when a user unlocks an achievement.
 * Listeners: HandleAchievementUnlockedForRewards, HandleAchievementNotification
 */
final class AchievementUnlocked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $userId,
        public readonly int    $achievementId,
        public readonly string $tier,
    ) {}
}
