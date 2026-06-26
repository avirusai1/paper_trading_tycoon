<?php

declare(strict_types=1);

namespace App\Trading\DTOs;

use App\Enums\OrderSide;
use App\Trading\Enums\OrderType;

/**
 * Paper Trading Tycoon — Trade Request DTO
 */
final readonly class TradeRequest
{
    public function __construct(
        public int $userId,
        public int $stockId,
        public string $symbol,
        public OrderSide $side,
        public OrderType $type,
        public int $quantity,
        public string $idempotencyKey,
        public ?int $limitPricePaise = null,
        public ?int $stopPricePaise = null,
    ) {}
}
