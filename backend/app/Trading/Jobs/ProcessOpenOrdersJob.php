<?php

declare(strict_types=1);

namespace App\Trading\Jobs;

use App\Enums\OrderSide;
use App\Events\TradeExecuted;
use App\MarketData\Services\MarketDataService;
use App\MarketData\ValueObjects\Ticker;
use App\Models\Order;
use App\Models\Wallet;
use App\Trading\Calculators\TradeCalculator;
use App\Trading\Contracts\HoldingRepositoryContract;
use App\Trading\Contracts\OrderRepositoryContract;
use App\Trading\Contracts\TradeRepositoryContract;
use App\Trading\Enums\OrderStatus;
use App\Trading\Enums\OrderType;
use App\Trading\Events\TradeSettled;
use App\Trading\Strategies\OrderStrategyRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Background job that checks and fills open limit, stop, and bracket orders.
 */
final class ProcessOpenOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(
        MarketDataService $marketDataService,
        OrderStrategyRegistry $strategyRegistry,
        OrderRepositoryContract $orderRepository,
        TradeRepositoryContract $tradeRepository,
        HoldingRepositoryContract $holdingRepository
    ): void {
        Log::info('[ProcessOpenOrdersJob] Starting execution checking open orders...');

        // 1. Fetch all open orders
        $openOrders = Order::query()->open()->get();

        if ($openOrders->isEmpty()) {
            return;
        }

        foreach ($openOrders as $order) {
            try {
                // 2. Fetch current quote
                $ticker = new Ticker($order->symbol);
                $quote = $marketDataService->getQuote($ticker);

                // 3. Resolve strategy and check trigger conditions
                $type = OrderType::from($order->order_type);
                $strategy = $strategyRegistry->resolve($type);

                if ($strategy->canFillOpenOrder($order, $quote)) {
                    DB::transaction(function () use ($order, $quote, $strategy, $orderRepository, $tradeRepository, $holdingRepository) {
                        $price = $strategy->determineExecutionPrice($order, $quote);
                        $qty = $order->remainingQuantity();
                        $brokerage = 0;

                        // Create trade fill
                        $trade = $tradeRepository->create($order, $qty, $price, $brokerage);

                        // Update order average price and status to Filled
                        $orderRepository->updateFills($order, $order->quantity, $price);
                        $orderRepository->updateStatus($order, OrderStatus::Filled);

                        // Wallet updates
                        $totalValue = $qty * $price;
                        $tax = TradeCalculator::tax($totalValue);
                        $fees = TradeCalculator::transactionFees($totalValue);

                        /** @var Wallet $wallet */
                        $wallet = Wallet::query()->where('user_id', $order->user_id)->firstOrFail();
                        $side = $order->side;
                        if (! $side instanceof OrderSide) {
                            $side = OrderSide::from((string) $side);
                        }

                        if ($side === OrderSide::Buy) {
                            $totalCost = $totalValue + $tax + $fees;
                            $wallet->virtual_cash_paise = max(0, $wallet->virtual_cash_paise - $totalCost);
                            $wallet->total_withdrawn_paise = max(0, $wallet->total_withdrawn_paise + $totalCost);
                        } else {
                            $totalGain = $totalValue - $tax - $fees;
                            $wallet->virtual_cash_paise = max(0, $wallet->virtual_cash_paise + $totalGain);
                        }
                        $wallet->save();

                        // Holdings update
                        $holdingRepository->updateHolding(
                            $order->user_id,
                            $order->stock_id,
                            $order->symbol,
                            $qty,
                            $price,
                            $side
                        );

                        // OCO Cancellation logic for Bracket child legs
                        $key = $order->idempotency_key;
                        $siblingKey = null;
                        if (str_ends_with($key, '-tp')) {
                            $siblingKey = substr($key, 0, -3).'-sl';
                        } elseif (str_ends_with($key, '-sl')) {
                            $siblingKey = substr($key, 0, -3).'-tp';
                        }

                        if ($siblingKey !== null) {
                            /** @var Order|null $sibling */
                            $sibling = Order::query()
                                ->open()
                                ->where('idempotency_key', $siblingKey)
                                ->first();

                            if ($sibling !== null) {
                                $orderRepository->updateStatus($sibling, OrderStatus::Cancelled);
                                Log::info('[ProcessOpenOrdersJob] Cancelled OCO sibling leg', [
                                    'order_id' => $sibling->id,
                                    'idempotency_key' => $siblingKey,
                                ]);
                            }
                        }

                        // Dispatch standard domain event (App\Events\TradeExecuted)
                        TradeExecuted::dispatch(
                            $order->user_id,
                            $order->symbol,
                            $side->value,
                            $qty,
                            $price,
                            (string) $trade->id
                        );

                        // Dispatch settled event
                        TradeSettled::dispatch(
                            $order->user_id,
                            $order->id,
                            $trade->id,
                            $order->symbol,
                            $side->value,
                            $qty,
                            $price,
                            $totalValue - $brokerage
                        );

                        Log::info('[ProcessOpenOrdersJob] Order successfully filled', [
                            'order_id' => $order->id,
                            'trade_id' => $trade->id,
                            'price' => $price,
                            'quantity' => $qty,
                        ]);
                    });
                }
            } catch (Throwable $e) {
                Log::error("[ProcessOpenOrdersJob] Failed to process open order ID {$order->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
