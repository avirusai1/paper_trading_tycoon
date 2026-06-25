<?php
declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when a trade fill is confirmed by the trading engine.
 * Listeners: HandleTradeExecutedForPortfolio, HandleTradeExecutedAntiCheat, HandleTradeExecutedAnalytics
 */
final class TradeExecuted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $userId,
        public readonly string $symbol,
        public readonly string $side,
        public readonly int    $quantity,
        public readonly int    $pricePaise,
        public readonly string $tradeId,
    ) {}
}
