<?php

declare(strict_types=1);

namespace App\MarketData\Contracts;

use App\MarketData\DTOs\HistoricalBar;
use App\MarketData\DTOs\SearchResult;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;
use App\Models\Stock;

interface StockRepositoryContract
{
    public function findStockByTicker(Ticker $ticker): ?Stock;

    public function getQuote(Ticker $ticker): ?StockQuote;

    public function saveQuote(StockQuote $quote): void;

    /**
     * @param  StockQuote[]  $quotes
     */
    public function saveQuotes(array $quotes): void;

    /**
     * @return HistoricalBar[]
     */
    public function getHistoricalData(Ticker $ticker, Timestamp $startDate, Timestamp $endDate): array;

    /**
     * @param  HistoricalBar[]  $bars
     */
    public function saveHistoricalBars(array $bars): void;

    /**
     * @return SearchResult[]
     */
    public function searchStocks(string $query): array;
}
