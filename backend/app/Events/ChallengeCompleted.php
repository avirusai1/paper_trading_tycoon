<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when a user completes and claims a mission/challenge reward.
 * Listeners: HandleChallengeCompletedForRewards, HandleChallengeNotification
 */
final class ChallengeCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $challengeId,
        public readonly array $rewardSummary,
    ) {}
}
