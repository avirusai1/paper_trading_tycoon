<?php

declare(strict_types=1);

namespace App\Trading\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when a trade has been successfully settled (wallet and holdings updated).
 */
final class TradeSettled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $orderId,
        public readonly int $tradeId,
        public readonly string $symbol,
        public readonly string $side,
        public readonly int $quantity,
        public readonly int $pricePaise,
        public readonly int $netValuePaise
    ) {}
}
