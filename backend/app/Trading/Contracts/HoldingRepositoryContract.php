<?php

declare(strict_types=1);

namespace App\Trading\Contracts;

use App\Enums\OrderSide;
use App\Models\Holding;

/**
 * Paper Trading Tycoon — Holding Repository Contract
 */
interface HoldingRepositoryContract
{
    public function updateHolding(
        int $userId,
        int $stockId,
        string $symbol,
        int $quantityChange,
        int $pricePaise,
        OrderSide $side
    ): Holding;
}
