<?php

declare(strict_types=1);

namespace App\DTOs\Trade;

use App\Enums\OrderSide;

/**
 * Paper Trading Tycoon — Place Order Data Transfer Object
 *
 * Carries validated, typed order data from the HTTP Request
 * to the TradingEngine. Prevents raw array passing between layers.
 */
final readonly class PlaceOrderDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $symbol,
        public readonly OrderSide $side,
        public readonly int $quantity,
        public readonly string $idempotencyKey,
    ) {}
}
