<?php

declare(strict_types=1);

namespace App\MarketData\Contracts;

use App\MarketData\DTOs\QuoteBatch;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\ValueObjects\Ticker;

interface QuoteProviderContract extends MarketDataProviderContract
{
    public function getQuote(Ticker $ticker): StockQuote;

    /**
     * @param  Ticker[]  $tickers
     */
    public function getQuotes(array $tickers): QuoteBatch;
}
