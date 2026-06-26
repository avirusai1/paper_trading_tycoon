<?php

declare(strict_types=1);

namespace App\Trading\DTOs;

use App\Enums\OrderSide;
use App\Trading\Enums\OrderStatus;

/**
 * Paper Trading Tycoon — Trade Result DTO
 */
final readonly class TradeResult
{
    public function __construct(
        public string $idempotencyKey,
        public int $userId,
        public OrderStatus $status,
        public string $symbol,
        public OrderSide $side,
        public int $quantity,
        public int $filledQuantity = 0,
        public ?int $orderId = null,
        public ?int $tradeId = null,
        public ?int $averageFillPricePaise = null,
        public ?int $totalValuePaise = null,
        public int $brokeragePaise = 0,
        public ?int $netValuePaise = null,
        public ?string $failureReason = null,
        public float $processingTimeMs = 0.0,
    ) {}

    public function isSuccess(): bool
    {
        return in_array($this->status, [OrderStatus::Filled, OrderStatus::PartiallyFilled, OrderStatus::Open], true);
    }
}
