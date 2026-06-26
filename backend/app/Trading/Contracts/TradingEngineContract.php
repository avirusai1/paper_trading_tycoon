<?php

declare(strict_types=1);

namespace App\Trading\Contracts;

use App\Trading\DTOs\TradeRequest;
use App\Trading\DTOs\TradeResult;

/**
 * Public interface for the Trading Engine subsystem.
 */
interface TradingEngineContract
{
    /**
     * Executes a virtual paper trade order.
     */
    public function execute(TradeRequest $request): TradeResult;
}
