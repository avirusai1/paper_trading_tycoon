<?php

declare(strict_types=1);

namespace App\Listeners\Trading;

use App\Enums\OrderSide;
use App\Events\TradeExecuted;
use App\GameEngine\Contracts\GameEngineContract;
use App\GameEngine\Events\TradeExecutedEvent as GameTradeExecutedEvent;
use App\Models\Trade;
use App\Portfolio\Contracts\PortfolioServiceContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Queued listener for TradeExecuted.
 * Recalculates user portfolio snapshot and triggers Game Engine.
 */
final class HandleTradeExecutedForPortfolio implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly PortfolioServiceContract $portfolioService,
        private readonly GameEngineContract $gameEngine
    ) {}

    public function handle(TradeExecuted $event): void
    {
        Log::info('[HandleTradeExecutedForPortfolio] Handling trade execution event...', [
            'user_id' => $event->userId,
            'symbol' => $event->symbol,
        ]);

        try {
            // 1. Recalculate and refresh portfolio using the central Portfolio Engine
            $this->portfolioService->refresh($event->userId);

            // 2. Invoke Game Engine for trade awards
            $tradeCount = Trade::query()->where('user_id', $event->userId)->count();
            $isFirstTrade = $tradeCount === 1;

            $gameEvent = new GameTradeExecutedEvent(
                userId: $event->userId,
                tradeId: $event->tradeId,
                symbol: $event->symbol,
                side: OrderSide::from($event->side),
                quantity: $event->quantity,
                pricePaise: $event->pricePaise,
                totalValuePaise: $event->quantity * $event->pricePaise,
                isFirstTrade: $isFirstTrade
            );

            $this->gameEngine->process($gameEvent);

        } catch (\Throwable $e) {
            Log::error('[HandleTradeExecutedForPortfolio] Failed to handle trade portfolio update', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
