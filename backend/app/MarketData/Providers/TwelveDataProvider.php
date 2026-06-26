<?php

declare(strict_types=1);

namespace App\MarketData\Providers;

use App\Enums\MarketStatus;
use App\MarketData\Contracts\HistoricalDataProviderContract;
use App\MarketData\Contracts\MarketStatusProviderContract;
use App\MarketData\Contracts\QuoteProviderContract;
use App\MarketData\Contracts\SearchProviderContract;
use App\MarketData\DTOs\HistoricalBar;
use App\MarketData\DTOs\MarketStatus as MarketStatusDTO;
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
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class TwelveDataProvider implements HistoricalDataProviderContract, MarketStatusProviderContract, QuoteProviderContract, SearchProviderContract
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) (config('market_data.providers.twelve_data.api_key') ?? '');
        $this->baseUrl = (string) (config('market_data.providers.twelve_data.base_url') ?? 'https://api.twelvedata.com');
    }

    public function getName(): string
    {
        return 'TwelveData';
    }

    public function isHealthy(): bool
    {
        try {
            if (empty($this->apiKey)) {
                return false;
            }
            $response = Http::timeout(3)->get("{$this->baseUrl}/api_usage", [
                'apikey' => $this->apiKey,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getQuote(Ticker $ticker): StockQuote
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Twelve Data API key is not configured');
        }

        $symbol = $this->formatSymbol($ticker);

        $response = Http::get("{$this->baseUrl}/quote", [
            'symbol' => $symbol,
            'apikey' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("Twelve Data quote request failed: {$response->body()}");
        }

        $data = $response->json();
        if (isset($data['status']) && $data['status'] === 'error') {
            throw new RuntimeException("Twelve Data API error: {$data['message']}");
        }

        return $this->mapQuote($data);
    }

    public function getQuotes(array $tickers): QuoteBatch
    {
        if (empty($tickers)) {
            return new QuoteBatch([]);
        }

        if (empty($this->apiKey)) {
            throw new RuntimeException('Twelve Data API key is not configured');
        }

        $symbols = array_map(fn ($t) => $this->formatSymbol($t), $tickers);
        $symbolsString = implode(',', $symbols);

        $response = Http::get("{$this->baseUrl}/quote", [
            'symbol' => $symbolsString,
            'apikey' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("Twelve Data batch quote request failed: {$response->body()}");
        }

        $data = $response->json();
        if (isset($data['status']) && $data['status'] === 'error') {
            throw new RuntimeException("Twelve Data API error: {$data['message']}");
        }

        $quotes = [];

        if (count($tickers) === 1) {
            $quotes[] = $this->mapQuote($data);
        } else {
            foreach ($data as $symbolKey => $quoteData) {
                if (isset($quoteData['status']) && $quoteData['status'] === 'error') {
                    continue;
                }
                $quotes[] = $this->mapQuote($quoteData);
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
            throw new RuntimeException('Twelve Data API key is not configured');
        }

        $symbol = $this->formatSymbol($ticker);
        $twelveInterval = match ($interval) {
            '1d' => '1day',
            '1w' => '1week',
            '1m' => '1month',
            default => '1day',
        };

        $response = Http::get("{$this->baseUrl}/time_series", [
            'symbol' => $symbol,
            'interval' => $twelveInterval,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'apikey' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("Twelve Data time series request failed: {$response->body()}");
        }

        $data = $response->json();
        if (isset($data['status']) && $data['status'] === 'error') {
            throw new RuntimeException("Twelve Data API error: {$data['message']}");
        }

        $values = $data['values'] ?? [];
        $bars = [];

        foreach ($values as $val) {
            $bars[] = new HistoricalBar(
                ticker: $ticker,
                open: Price::fromRupees($val['open']),
                high: Price::fromRupees($val['high']),
                low: Price::fromRupees($val['low']),
                close: Price::fromRupees($val['close']),
                volume: new Volume((int) $val['volume']),
                timestamp: new Timestamp($val['datetime'])
            );
        }

        return $bars;
    }

    public function searchStocks(string $query): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Twelve Data API key is not configured');
        }

        $response = Http::get("{$this->baseUrl}/symbol_search", [
            'symbol' => $query,
            'apikey' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("Twelve Data search request failed: {$response->body()}");
        }

        $data = $response->json();
        $dataList = $data['data'] ?? [];
        $results = [];

        foreach ($dataList as $item) {
            $exchangeVal = strtoupper($item['exchange'] ?? '');
            if ($exchangeVal !== 'NSE' && $exchangeVal !== 'BSE') {
                continue;
            }

            $results[] = new SearchResult(
                ticker: new Ticker($item['symbol']),
                name: new CompanyName($item['instrument_name'] ?? $item['symbol']),
                exchange: new Exchange($exchangeVal),
                isin: $item['isin'] ?? 'INE000000000',
                sector: null,
                industry: null
            );
        }

        return $results;
    }

    public function getMarketStatus(Exchange $exchange): MarketStatusDTO
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Twelve Data API key is not configured');
        }

        $response = Http::get("{$this->baseUrl}/market_state", [
            'exchange' => $exchange->value,
            'apikey' => $this->apiKey,
        ]);

        $status = MarketStatus::Closed;
        $isOpen = false;

        if ($response->successful()) {
            $data = $response->json();
            $state = $data[0] ?? $data;
            if (isset($state['is_market_open'])) {
                $isOpen = (bool) $state['is_market_open'];
                $status = $isOpen ? MarketStatus::Open : MarketStatus::Closed;
            }
        }

        return new MarketStatusDTO(
            exchange: $exchange,
            status: $status,
            isOpen: $isOpen,
            nextOpening: null,
            nextClosing: null
        );
    }

    private function formatSymbol(Ticker $ticker): string
    {
        return $ticker->symbol;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function mapQuote(array $data): StockQuote
    {
        $symbol = $data['symbol'] ?? '';
        $ltp = $data['close'] ?? $data['price'] ?? '0';
        $open = $data['open'] ?? '0';
        $high = $data['high'] ?? '0';
        $low = $data['low'] ?? '0';
        $close = $data['previous_close'] ?? '0';
        $change = $data['change'] ?? '0';
        $changePercent = $data['percent_change'] ?? '0';
        $volume = $data['volume'] ?? '0';
        $datetime = $data['datetime'] ?? 'now';

        return new StockQuote(
            ticker: new Ticker($symbol),
            ltp: Price::fromRupees($ltp),
            open: Price::fromRupees($open),
            high: Price::fromRupees($high),
            low: Price::fromRupees($low),
            close: Price::fromRupees($close),
            change: Price::fromRupees(abs((float) $change)),
            changePercent: new Percentage((float) $changePercent),
            volume: new Volume((int) $volume),
            marketStatus: MarketStatus::Open,
            quotedAt: new Timestamp($datetime)
        );
    }
}
