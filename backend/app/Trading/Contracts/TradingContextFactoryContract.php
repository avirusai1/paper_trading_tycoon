<?php

declare(strict_types=1);

namespace App\Trading\Contracts;

use App\Trading\Contexts\TradingContext;
use App\Trading\DTOs\TradeRequest;

/**
 * Paper Trading Tycoon — Trading Context Factory Contract
 */
interface TradingContextFactoryContract
{
    public function build(TradeRequest $request): TradingContext;
}
