<?php

declare(strict_types=1);

namespace App\Trading\Contracts;

use App\Models\Order;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\OrderStatus;

/**
 * Paper Trading Tycoon — Order Repository Contract
 */
interface OrderRepositoryContract
{
    public function find(int $id): ?Order;

    public function create(TradeRequest $request, OrderStatus $status): Order;

    public function updateStatus(Order $order, OrderStatus $status, ?string $rejectionReason = null): void;

    public function updateFills(Order $order, int $filledQuantity, int $averageFillPricePaise): void;
}
