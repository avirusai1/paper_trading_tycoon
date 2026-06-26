<?php

declare(strict_types=1);

namespace App\Trading\Repositories;

use App\Models\Order;
use App\Trading\Contracts\OrderRepositoryContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\OrderStatus;

/**
 * Eloquent implementation of OrderRepositoryContract.
 */
final class OrderRepository implements OrderRepositoryContract
{
    public function find(int $id): ?Order
    {
        return Order::query()->find($id);
    }

    public function create(TradeRequest $request, OrderStatus $status): Order
    {
        return Order::query()->create([
            'user_id' => $request->userId,
            'stock_id' => $request->stockId,
            'symbol' => $request->symbol,
            'idempotency_key' => $request->idempotencyKey,
            'side' => $request->side,
            'order_type' => $request->type->value,
            'status' => $status->value,
            'quantity' => $request->quantity,
            'filled_quantity' => 0,
            'limit_price_paise' => $request->limitPricePaise,
            'stop_price_paise' => $request->stopPricePaise,
            'average_fill_price_paise' => null,
            'rejection_reason' => null,
            'expires_at' => null,
        ]);
    }

    public function updateStatus(Order $order, OrderStatus $status, ?string $rejectionReason = null): void
    {
        $order->update([
            'status' => $status->value,
            'rejection_reason' => $rejectionReason,
        ]);
    }

    public function updateFills(Order $order, int $filledQuantity, int $averageFillPricePaise): void
    {
        $order->update([
            'filled_quantity' => $filledQuantity,
            'average_fill_price_paise' => $averageFillPricePaise,
        ]);
    }
}
