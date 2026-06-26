<?php

declare(strict_types=1);

namespace App\MarketData\Contracts;

use App\MarketData\DTOs\SearchResult;

interface SearchProviderContract extends MarketDataProviderContract
{
    /**
     * @return SearchResult[]
     */
    public function searchStocks(string $query): array;
}
