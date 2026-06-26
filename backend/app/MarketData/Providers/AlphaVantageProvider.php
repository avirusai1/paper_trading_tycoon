<?php

declare(strict_types=1);

namespace App\MarketData\Providers;

use App\Enums\MarketStatus;
use App\MarketData\Contracts\HistoricalDataProviderContract;
use App\MarketData\Contracts\QuoteProviderContract;
use App\MarketData\Contracts\SearchProviderContract;
use App\MarketData\DTOs\HistoricalBar;
use App\MarketData\DTOs\QuoteBatch;
use App\MarketData\DTOs\SearchResult;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\ValueObjects\CompanyName;
use App\MarketData\ValueObjects\Exchange;
use App\MarketData\ValueObjects\Percentage;
use App\MarketData\ValueObjects\Price;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;
use App\MarketData\ValueObjects\Volume;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class AlphaVantageProvider implements HistoricalDataProviderContract, QuoteProviderContract, SearchProviderContract
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) (config('market_data.providers.alpha_vantage.api_key') ?? '');
        $this->baseUrl = (string) (config('market_data.providers.alpha_vantage.base_url') ?? 'https://www.alphavantage.co');
    }

    public function getName(): string
    {
        return 'AlphaVantage';
    }

    public function isHealthy(): bool
    {
        try {
            if (empty($this->apiKey)) {
                return false;
            }
            $response = Http::timeout(3)->get("{$this->baseUrl}/query", [
                'function' => 'SYMBOL_SEARCH',
                'keywords' => 'RELIANCE',
                'apikey' => $this->apiKey,
            ]);

            return $response->successful() && ! isset($response->json()['Note']);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getQuote(Ticker $ticker): StockQuote
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Alpha Vantage API key is not configured');
        }

        $symbol = $this->formatSymbol($ticker);

        $response = Http::get("{$this->baseUrl}/query", [
            'function' => 'GLOBAL_QUOTE',
            'symbol' => $symbol,
            'apikey' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("Alpha Vantage quote request failed: {$response->body()}");
        }

        $data = $response->json();
        if (isset($data['Note'])) {
            throw new RuntimeException("Alpha Vantage API rate limited: {$data['Note']}");
        }

        $quoteData = $data['Global Quote'] ?? null;
        if (empty($quoteData) || ! isset($quoteData['01. symbol'])) {
            throw new RuntimeException('Alpha Vantage returned invalid quote data: '.json_encode($data));
        }

        return $this->mapQuote($quoteData);
    }

    public function getQuotes(array $tickers): QuoteBatch
    {
        $quotes = [];
        foreach ($tickers as $ticker) {
            try {
                $quotes[] = $this->getQuote($ticker);
            } catch (\Exception $e) {
                continue;
            }
        }

        return new QuoteBatch($quotes);
    }

    public function getHistoricalData(
        Ticker $ticker,
        Timestamp $startDate,
        Timestamp $endDate,
        string $interval = '1d'
    ): array {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Alpha Vantage API key is not configured');
        }

        $symbol = $this->formatSymbol($ticker);
        $function = 'TIME_SERIES_DAILY';

        $response = Http::get("{$this->baseUrl}/query", [
            'function' => $function,
            'symbol' => $symbol,
            'apikey' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("Alpha Vantage time series request failed: {$response->body()}");
        }

        $data = $response->json();
        if (isset($data['Note'])) {
            throw new RuntimeException("Alpha Vantage API rate limited: {$data['Note']}");
        }

        $timeSeries = $data['Time Series (Daily)'] ?? null;
        if (empty($timeSeries)) {
            throw new RuntimeException('Alpha Vantage returned invalid time series data: '.json_encode($data));
        }

        $bars = [];
        $start = $startDate->value;
        $end = $endDate->value;

        foreach ($timeSeries as $dateStr => $candle) {
            $date = Carbon::parse($dateStr);
            if ($date->between($start, $end)) {
                $bars[] = new HistoricalBar(
                    ticker: $ticker,
                    open: Price::fromRupees($candle['1. open']),
                    high: Price::fromRupees($candle['2. high']),
                    low: Price::fromRupees($candle['3. low']),
                    close: Price::fromRupees($candle['4. close']),
                    volume: new Volume((int) $candle['5. volume']),
                    timestamp: new Timestamp($date->setTime(15, 30, 0))
                );
            }
        }

        return $bars;
    }

    public function searchStocks(string $query): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Alpha Vantage API key is not configured');
        }

        $response = Http::get("{$this->baseUrl}/query", [
            'function' => 'SYMBOL_SEARCH',
            'keywords' => $query,
            'apikey' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("Alpha Vantage search request failed: {$response->body()}");
        }

        $data = $response->json();
        if (isset($data['Note'])) {
            throw new RuntimeException("Alpha Vantage API rate limited: {$data['Note']}");
        }

        $matches = $data['bestMatches'] ?? [];
        $results = [];

        foreach ($matches as $match) {
            $rawExchange = $match['4. region'] ?? '';
            $exchangeVal = 'NSE';
            if (stripos($rawExchange, 'India') !== false || stripos($rawExchange, 'NSE') !== false || stripos($rawExchange, 'BOM') !== false) {
                $exchangeVal = stripos($rawExchange, 'BOM') !== false ? 'BSE' : 'NSE';
            } else {
                continue;
            }

            $symbol = $match['1. symbol'] ?? '';
            $parts = explode('.', $symbol);
            $cleanSymbol = $parts[0];

            $results[] = new SearchResult(
                ticker: new Ticker($cleanSymbol),
                name: new CompanyName($match['2. name'] ?? $cleanSymbol),
                exchange: new Exchange($exchangeVal),
                isin: 'INE000000000',
                sector: null,
                industry: null
            );
        }

        return $results;
    }

    private function formatSymbol(Ticker $ticker): string
    {
        return $ticker->symbol.'.BSE';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function mapQuote(array $data): StockQuote
    {
        $symbolRaw = $data['01. symbol'] ?? '';
        $parts = explode('.', $symbolRaw);
        $symbol = $parts[0];

        $open = $data['02. open'] ?? '0';
        $high = $data['03. high'] ?? '0';
        $low = $data['04. low'] ?? '0';
        $ltp = $data['05. price'] ?? '0';
        $volume = $data['06. volume'] ?? '0';
        $date = $data['07. latest trading day'] ?? 'now';
        $close = $data['08. previous close'] ?? '0';
        $change = $data['09. change'] ?? '0';
        $changePercentRaw = $data['10. change percent'] ?? '0%';
        $changePercent = floatval(rtrim($changePercentRaw, '%'));

        return new StockQuote(
            ticker: new Ticker($symbol),
            ltp: Price::fromRupees($ltp),
            open: Price::fromRupees($open),
            high: Price::fromRupees($high),
            low: Price::fromRupees($low),
            close: Price::fromRupees($close),
            change: Price::fromRupees(abs((float) $change)),
            changePercent: new Percentage($changePercent),
            volume: new Volume((int) $volume),
            marketStatus: MarketStatus::Open,
            quotedAt: new Timestamp($date)
        );
    }
}
