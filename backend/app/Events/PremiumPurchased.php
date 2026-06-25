<?php
declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * Dispatched when a user purchases or renews a premium subscription.
 */
final class PremiumPurchased
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $userId,
        public readonly string $plan,
        public readonly Carbon $expiresAt,
    ) {}
}
