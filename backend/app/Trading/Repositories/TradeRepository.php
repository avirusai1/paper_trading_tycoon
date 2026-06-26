<?php

declare(strict_types=1);

namespace App\Trading\Repositories;

use App\Models\Order;
use App\Models\Trade;
use App\Trading\Contracts\TradeRepositoryContract;
use Carbon\Carbon;

/**
 * Eloquent implementation of TradeRepositoryContract.
 */
final class TradeRepository implements TradeRepositoryContract
{
    public function create(Order $order, int $quantity, int $pricePaise, int $brokeragePaise): Trade
    {
        $totalValuePaise = $quantity * $pricePaise;
        $netValuePaise = $totalValuePaise - $brokeragePaise;

        return Trade::query()->create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'stock_id' => $order->stock_id,
            'symbol' => $order->symbol,
            'side' => $order->side,
            'quantity' => $quantity,
            'price_paise' => $pricePaise,
            'total_value_paise' => $totalValuePaise,
            'brokerage_paise' => $brokeragePaise,
            'net_value_paise' => $netValuePaise,
            'executed_at' => Carbon::now(),
        ]);
    }
}
