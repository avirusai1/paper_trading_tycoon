<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched after XP is successfully credited to a user's account.
 * Listeners: HandleXPGrantedForChallenges
 */
final class XPGranted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $amount,
        public readonly string $source,
        public readonly string $sourceId,
    ) {}
}
