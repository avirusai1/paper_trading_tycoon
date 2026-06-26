<?php

declare(strict_types=1);

namespace App\GameEngine\Events;

use App\Enums\OrderSide;
use App\GameEngine\Enums\GameEventType;

/**
 * Raised when a trade fill is confirmed.
 *
 * NOTE: This is the Game Engine's internal event. It is distinct from the
 * application-layer App\Events\TradeExecuted which is dispatched to the
 * Laravel event bus. The listener for App\Events\TradeExecuted is responsible
 * for constructing this DTO and calling GameEngine::process().
 */
final readonly class TradeExecutedEvent implements GameEvent
{
    public function __construct(
        private readonly int $userId,
        private readonly string $tradeId,
        public readonly string $symbol,
        public readonly OrderSide $side,
        public readonly int $quantity,
        /** Fill price per unit in paise. */
        public readonly int $pricePaise,
        /** Total fill value (quantity × pricePaise) in paise. */
        public readonly int $totalValuePaise,
        public readonly bool $isFirstTrade,
    ) {}

    public function eventType(): GameEventType
    {
        return GameEventType::TradeExecuted;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function sourceId(): string
    {
        return $this->tradeId;
    }

    public function idempotencyKey(): string
    {
        return sprintf('TradeExecutedEvent:%d:%s', $this->userId, $this->tradeId);
    }
}
