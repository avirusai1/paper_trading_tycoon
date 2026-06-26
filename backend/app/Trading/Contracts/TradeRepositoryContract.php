<?php

declare(strict_types=1);

namespace App\Trading\Contracts;

use App\Models\Order;
use App\Models\Trade;

/**
 * Paper Trading Tycoon — Trade Repository Contract
 */
interface TradeRepositoryContract
{
    public function create(Order $order, int $quantity, int $pricePaise, int $brokeragePaise): Trade;
}
