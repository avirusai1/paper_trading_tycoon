<?php

declare(strict_types=1);

namespace App\Trading\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when a trade request fails validation or execution.
 */
final class TradeFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $symbol,
        public readonly string $side,
        public readonly string $idempotencyKey,
        public readonly string $reason,
        public readonly string $message
    ) {}
}
