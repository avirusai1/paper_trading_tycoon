<?php

declare(strict_types=1);

namespace App\Trading\Pipelines;

use App\Enums\OrderSide;
use App\Events\TradeExecuted;
use App\Trading\Calculators\TradeCalculator;
use App\Trading\Contracts\HoldingRepositoryContract;
use App\Trading\Contracts\OrderRepositoryContract;
use App\Trading\Contracts\TradeRepositoryContract;
use App\Trading\Contracts\TradingContextFactoryContract;
use App\Trading\Contracts\TradingValidatorContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\DTOs\TradeResult;
use App\Trading\Enums\OrderStatus;
use App\Trading\Enums\OrderType;
use App\Trading\Events\TradeFailed;
use App\Trading\Events\TradeSettled;
use App\Trading\Exceptions\TradingValidationException;
use App\Trading\Strategies\OrderStrategyRegistry;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Coordinates context building, validation, order execution, settlement, and events.
 */
final readonly class TradingPipeline
{
    /**
     * @param  TradingValidatorContract[]  $validators
     */
    public function __construct(
        private TradingContextFactoryContract $contextFactory,
        private OrderStrategyRegistry $strategyRegistry,
        private OrderRepositoryContract $orderRepository,
        private TradeRepositoryContract $tradeRepository,
        private HoldingRepositoryContract $holdingRepository,
        private array $validators,
    ) {}

    public function execute(TradeRequest $request): TradeResult
    {
        $startTime = microtime(true);

        try {
            // 1. Build context
            $context = $this->contextFactory->build($request);

            // 2. Validate
            foreach ($this->validators as $validator) {
                $validator->validate($request, $context);
            }

            // 3. Resolve execution strategy
            $strategy = $this->strategyRegistry->resolve($request->type);
            $canFill = $strategy->canFillImmediately($request, $context);

            // 4. Atomic database writes
            $result = DB::transaction(function () use ($request, $context, $strategy, $canFill) {
                if ($canFill) {
                    // Create filled order
                    $order = $this->orderRepository->create($request, OrderStatus::Filled);

                    // Determine execution price
                    $price = $strategy->determineExecutionPrice($request, $context->quote);

                    // Create trade record
                    $brokerage = 0;
                    $trade = $this->tradeRepository->create($order, $request->quantity, $price, $brokerage);

                    // Update order average price and filled qty
                    $this->orderRepository->updateFills($order, $request->quantity, $price);

                    // Wallet updates
                    $totalValue = $request->quantity * $price;
                    $tax = TradeCalculator::tax($totalValue);
                    $fees = TradeCalculator::transactionFees($totalValue);

                    $wallet = $context->wallet;
                    if ($request->side === OrderSide::Buy) {
                        $totalCost = $totalValue + $tax + $fees;
                        $wallet->virtual_cash_paise = max(0, $wallet->virtual_cash_paise - $totalCost);
                        $wallet->total_withdrawn_paise = max(0, $wallet->total_withdrawn_paise + $totalCost);
                    } else {
                        // Sell
                        $totalGain = $totalValue - $tax - $fees;
                        $wallet->virtual_cash_paise = max(0, $wallet->virtual_cash_paise + $totalGain);
                    }
                    $wallet->save();

                    // Update Holdings position
                    $this->holdingRepository->updateHolding(
                        $request->userId,
                        $request->stockId,
                        $request->symbol,
                        $request->quantity,
                        $price,
                        $request->side
                    );

                    // If Bracket order, set up Take Profit and Stop Loss legs
                    if ($request->type === OrderType::Bracket) {
                        // Limit Leg (Take Profit)
                        $tpRequest = new TradeRequest(
                            userId: $request->userId,
                            stockId: $request->stockId,
                            symbol: $request->symbol,
                            side: $request->side === OrderSide::Buy ? OrderSide::Sell : OrderSide::Buy,
                            type: OrderType::Limit,
                            quantity: $request->quantity,
                            idempotencyKey: $request->idempotencyKey.'-tp',
                            limitPricePaise: $request->limitPricePaise,
                            stopPricePaise: null
                        );
                        $this->orderRepository->create($tpRequest, OrderStatus::Open);

                        // Stop Leg (Stop Loss)
                        $slRequest = new TradeRequest(
                            userId: $request->userId,
                            stockId: $request->stockId,
                            symbol: $request->symbol,
                            side: $request->side === OrderSide::Buy ? OrderSide::Sell : OrderSide::Buy,
                            type: OrderType::Stop,
                            quantity: $request->quantity,
                            idempotencyKey: $request->idempotencyKey.'-sl',
                            limitPricePaise: null,
                            stopPricePaise: $request->stopPricePaise
                        );
                        $this->orderRepository->create($slRequest, OrderStatus::Open);
                    }

                    // Dispatch standard domain event (App\Events\TradeExecuted)
                    TradeExecuted::dispatch(
                        $request->userId,
                        $request->symbol,
                        $request->side->value,
                        $request->quantity,
                        $price,
                        (string) $trade->id
                    );

                    // Dispatch subsystem-specific settled event
                    TradeSettled::dispatch(
                        $request->userId,
                        $order->id,
                        $trade->id,
                        $request->symbol,
                        $request->side->value,
                        $request->quantity,
                        $price,
                        $totalValue - $brokerage
                    );

                    return new TradeResult(
                        idempotencyKey: $request->idempotencyKey,
                        userId: $request->userId,
                        status: OrderStatus::Filled,
                        symbol: $request->symbol,
                        side: $request->side,
                        quantity: $request->quantity,
                        filledQuantity: $request->quantity,
                        orderId: $order->id,
                        tradeId: $trade->id,
                        averageFillPricePaise: $price,
                        totalValuePaise: $totalValue,
                        brokeragePaise: $brokerage,
                        netValuePaise: $totalValue - $brokerage
                    );
                } else {
                    // Order cannot fill immediately, create open/pending order
                    $order = $this->orderRepository->create($request, OrderStatus::Open);

                    return new TradeResult(
                        idempotencyKey: $request->idempotencyKey,
                        userId: $request->userId,
                        status: OrderStatus::Open,
                        symbol: $request->symbol,
                        side: $request->side,
                        quantity: $request->quantity,
                        filledQuantity: 0,
                        orderId: $order->id,
                        tradeId: null
                    );
                }
            });

            $elapsed = round((microtime(true) - $startTime) * 1000, 2);

            return new TradeResult(
                idempotencyKey: $result->idempotencyKey,
                userId: $result->userId,
                status: $result->status,
                symbol: $result->symbol,
                side: $result->side,
                quantity: $result->quantity,
                filledQuantity: $result->filledQuantity,
                orderId: $result->orderId,
                tradeId: $result->tradeId,
                averageFillPricePaise: $result->averageFillPricePaise,
                totalValuePaise: $result->totalValuePaise,
                brokeragePaise: $result->brokeragePaise,
                netValuePaise: $result->netValuePaise,
                failureReason: null,
                processingTimeMs: $elapsed
            );

        } catch (TradingValidationException $e) {
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);
            TradeFailed::dispatch(
                $request->userId,
                $request->symbol,
                $request->side->value,
                $request->idempotencyKey,
                $e->reason->value,
                $e->getMessage()
            );

            return new TradeResult(
                idempotencyKey: $request->idempotencyKey,
                userId: $request->userId,
                status: OrderStatus::Rejected,
                symbol: $request->symbol,
                side: $request->side,
                quantity: $request->quantity,
                failureReason: $e->getMessage(),
                processingTimeMs: $elapsed
            );
        } catch (Throwable $e) {
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);
            TradeFailed::dispatch(
                $request->userId,
                $request->symbol,
                $request->side->value,
                $request->idempotencyKey,
                'execution_failed',
                $e->getMessage()
            );

            return new TradeResult(
                idempotencyKey: $request->idempotencyKey,
                userId: $request->userId,
                status: OrderStatus::Rejected,
                symbol: $request->symbol,
                side: $request->side,
                quantity: $request->quantity,
                failureReason: 'Execution error: '.$e->getMessage(),
                processingTimeMs: $elapsed
            );
        }
    }
}
