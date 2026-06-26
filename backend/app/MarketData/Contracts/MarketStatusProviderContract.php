<?php

declare(strict_types=1);

namespace App\MarketData\Contracts;

use App\MarketData\DTOs\MarketStatus;
use App\MarketData\ValueObjects\Exchange;

interface MarketStatusProviderContract extends MarketDataProviderContract
{
    public function getMarketStatus(Exchange $exchange): MarketStatus;
}
