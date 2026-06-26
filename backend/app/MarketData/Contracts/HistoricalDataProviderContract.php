<?php

declare(strict_types=1);

namespace App\MarketData\Contracts;

use App\MarketData\DTOs\HistoricalBar;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;

interface HistoricalDataProviderContract extends MarketDataProviderContract
{
    /**
     * @return HistoricalBar[]
     */
    public function getHistoricalData(
        Ticker $ticker,
        Timestamp $startDate,
        Timestamp $endDate,
        string $interval = '1d'
    ): array;
}
