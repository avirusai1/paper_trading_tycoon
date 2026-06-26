<?php

declare(strict_types=1);

namespace App\MarketData\Services;

use App\MarketData\Actions\GetCorporateActionsAction;
use App\MarketData\Actions\GetHistoricalDataAction;
use App\MarketData\Actions\GetMarketStatusAction;
use App\MarketData\Actions\GetMarketSummaryAction;
use App\MarketData\Actions\GetQuoteAction;
use App\MarketData\Actions\GetTopGainersAction;
use App\MarketData\Actions\GetTopLosersAction;
use App\MarketData\Actions\GetTrendingStocksAction;
use App\MarketData\Actions\GetWatchlistQuotesAction;
use App\MarketData\Actions\SearchStocksAction;
use App\MarketData\DTOs\CorporateAction;
use App\MarketData\DTOs\HistoricalBar;
use App\MarketData\DTOs\MarketStatus;
use App\MarketData\DTOs\MarketSummary;
use App\MarketData\DTOs\SearchResult;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\DTOs\WatchlistQuote;
use App\MarketData\ValueObjects\Exchange;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;

final readonly class MarketDataService
{
    public function __construct(
        private GetQuoteAction $getQuoteAction,
        private GetHistoricalDataAction $getHistoricalDataAction,
        private SearchStocksAction $searchStocksAction,
        private GetMarketSummaryAction $getMarketSummaryAction,
        private GetWatchlistQuotesAction $getWatchlistQuotesAction,
        private GetTopGainersAction $getTopGainersAction,
        private GetTopLosersAction $getTopLosersAction,
        private GetTrendingStocksAction $getTrendingStocksAction,
        private GetMarketStatusAction $getMarketStatusAction,
        private GetCorporateActionsAction $getCorporateActionsAction
    ) {}

    public function getQuote(Ticker $ticker): StockQuote
    {
        return $this->getQuoteAction->execute($ticker);
    }

    /**
     * @return HistoricalBar[]
     */
    public function getHistoricalData(Ticker $ticker, Timestamp $startDate, Timestamp $endDate, string $interval = '1d'): array
    {
        return $this->getHistoricalDataAction->execute($ticker, $startDate, $endDate, $interval);
    }

    /**
     * @return SearchResult[]
     */
    public function searchStocks(string $query): array
    {
        return $this->searchStocksAction->execute($query);
    }

    public function getMarketSummary(): MarketSummary
    {
        return $this->getMarketSummaryAction->execute();
    }

    /**
     * @param  Ticker[]  $tickers
     * @return WatchlistQuote[]
     */
    public function getWatchlistQuotes(array $tickers): array
    {
        return $this->getWatchlistQuotesAction->execute($tickers);
    }

    /**
     * @return StockQuote[]
     */
    public function getTopGainers(int $limit = 5): array
    {
        return $this->getTopGainersAction->execute($limit);
    }

    /**
     * @return StockQuote[]
     */
    public function getTopLosers(int $limit = 5): array
    {
        return $this->getTopLosersAction->execute($limit);
    }

    /**
     * @return StockQuote[]
     */
    public function getTrendingStocks(int $limit = 5): array
    {
        return $this->getTrendingStocksAction->execute($limit);
    }

    public function getMarketStatus(Exchange $exchange): MarketStatus
    {
        return $this->getMarketStatusAction->execute($exchange);
    }

    /**
     * @return CorporateAction[]
     */
    public function getCorporateActions(Ticker $ticker): array
    {
        return $this->getCorporateActionsAction->execute($ticker);
    }
}
