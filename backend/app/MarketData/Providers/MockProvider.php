<?php

declare(strict_types=1);

namespace App\MarketData\Providers;

use App\Enums\MarketStatus;
use App\MarketData\Contracts\CorporateActionProviderContract;
use App\MarketData\Contracts\HistoricalDataProviderContract;
use App\MarketData\Contracts\MarketStatusProviderContract;
use App\MarketData\Contracts\QuoteProviderContract;
use App\MarketData\Contracts\SearchProviderContract;
use App\MarketData\DTOs\CorporateAction;
use App\MarketData\DTOs\HistoricalBar;
use App\MarketData\DTOs\MarketStatus as MarketStatusDTO;
use App\MarketData\DTOs\QuoteBatch;
use App\MarketData\DTOs\SearchResult;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\ValueObjects\CompanyName;
use App\MarketData\ValueObjects\Exchange;
use App\MarketData\ValueObjects\Industry;
use App\MarketData\ValueObjects\Percentage;
use App\MarketData\ValueObjects\Price;
use App\MarketData\ValueObjects\Sector;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;
use App\MarketData\ValueObjects\Volume;
use App\Models\Stock;
use App\Models\StockPrice;
use Carbon\Carbon;

final class MockProvider implements CorporateActionProviderContract, HistoricalDataProviderContract, MarketStatusProviderContract, QuoteProviderContract, SearchProviderContract
{
    public function getName(): string
    {
        return 'MockProvider';
    }

    public function isHealthy(): bool
    {
        return true;
    }

    public function getQuote(Ticker $ticker): StockQuote
    {
        // Try to load stock and default price from DB
        $stock = Stock::where('symbol', $ticker->symbol)->first();
        $basePricePaise = 100000; // default 1000 Rs
        $openPaise = 100000;
        $highPaise = 101000;
        $lowPaise = 99000;
        $volumeVal = 500000;

        if ($stock) {
            $stockPrice = StockPrice::where('stock_id', $stock->id)->first();
            if ($stockPrice) {
                $basePricePaise = $stockPrice->ltp_paise;
                $openPaise = $stockPrice->open_paise ?: (int) ($basePricePaise * 0.995);
                $highPaise = $stockPrice->high_paise ?: (int) ($basePricePaise * 1.01);
                $lowPaise = $stockPrice->low_paise ?: (int) ($basePricePaise * 0.99);
                $volumeVal = $stockPrice->volume ?: 1000000;
            }
        }

        // Apply a small fluctuation (e.g. -1.5% to +1.5%)
        $fluctuationPercent = (random_int(-150, 150) / 10000.0);
        $ltpPaise = (int) ($basePricePaise * (1 + $fluctuationPercent));
        if ($ltpPaise < 100) {
            $ltpPaise = 100; // minimum 1 Re
        }

        if ($ltpPaise > $highPaise) {
            $highPaise = $ltpPaise;
        }
        if ($ltpPaise < $lowPaise) {
            $lowPaise = $ltpPaise;
        }

        $changePaise = $ltpPaise - $openPaise;
        $changePercentVal = ($openPaise > 0) ? ($changePaise / $openPaise) * 100 : 0.0;

        // Current status based on time
        $status = $this->determineMarketStatus(new Exchange('NSE'));

        return new StockQuote(
            ticker: $ticker,
            ltp: new Price($ltpPaise),
            open: new Price($openPaise),
            high: new Price($highPaise),
            low: new Price($lowPaise),
            close: new Price($basePricePaise),
            change: new Price(abs($changePaise)),
            changePercent: new Percentage($changePercentVal),
            volume: new Volume($volumeVal + random_int(100, 10000)),
            marketStatus: $status,
            quotedAt: Timestamp::now()
        );
    }

    public function getQuotes(array $tickers): QuoteBatch
    {
        $quotes = [];
        foreach ($tickers as $ticker) {
            $quotes[] = $this->getQuote($ticker);
        }

        return new QuoteBatch($quotes);
    }

    public function getHistoricalData(
        Ticker $ticker,
        Timestamp $startDate,
        Timestamp $endDate,
        string $interval = '1d'
    ): array {
        $stock = Stock::where('symbol', $ticker->symbol)->first();
        $basePricePaise = 100000;
        if ($stock) {
            $stockPrice = StockPrice::where('stock_id', $stock->id)->first();
            if ($stockPrice) {
                $basePricePaise = $stockPrice->ltp_paise;
            }
        }

        $bars = [];
        $currentDate = $startDate->value->copy();
        $end = $endDate->value;

        // Ensure we don't loop forever
        $maxDays = 365;
        $daysCount = 0;

        while ($currentDate->lte($end) && $daysCount < $maxDays) {
            // Only generate data for weekdays (Mon-Fri)
            if ($currentDate->isWeekday()) {
                // Determine prices with some variance
                $variance = random_int(-300, 320) / 10000.0; // -3% to +3.2%
                $openPaise = (int) ($basePricePaise * (1 + (random_int(-100, 100) / 10000.0)));
                $closePaise = (int) ($openPaise * (1 + $variance));
                $highPaise = (int) (max($openPaise, $closePaise) * (1 + (random_int(0, 150) / 10000.0)));
                $lowPaise = (int) (min($openPaise, $closePaise) * (1 - (random_int(0, 150) / 10000.0)));

                if ($openPaise < 100) {
                    $openPaise = 100;
                }
                if ($closePaise < 100) {
                    $closePaise = 100;
                }
                if ($highPaise < 100) {
                    $highPaise = 100;
                }
                if ($lowPaise < 100) {
                    $lowPaise = 100;
                }

                $bars[] = new HistoricalBar(
                    ticker: $ticker,
                    open: new Price($openPaise),
                    high: new Price($highPaise),
                    low: new Price($lowPaise),
                    close: new Price($closePaise),
                    volume: new Volume(random_int(500000, 5000000)),
                    timestamp: new Timestamp($currentDate->copy()->setTime(15, 30, 0))
                );

                // close becomes the new base price for the next day
                $basePricePaise = $closePaise;
            }
            $currentDate->addDay();
            $daysCount++;
        }

        return $bars;
    }

    public function searchStocks(string $query): array
    {
        $stocks = Stock::where('symbol', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->active()
            ->get();

        $results = [];
        foreach ($stocks as $stock) {
            $results[] = new SearchResult(
                ticker: new Ticker($stock->symbol),
                name: new CompanyName($stock->name),
                exchange: new Exchange($stock->exchange),
                isin: $stock->isin ?? 'INE000000000',
                sector: $stock->sector ? new Sector($stock->sector) : null,
                industry: $stock->industry ? new Industry($stock->industry) : null
            );
        }

        return $results;
    }

    public function getMarketStatus(Exchange $exchange): MarketStatusDTO
    {
        $status = $this->determineMarketStatus($exchange);
        $isOpen = ($status === MarketStatus::Open);

        $now = Carbon::now('Asia/Kolkata');
        $nextOpening = $now->copy();
        $nextClosing = $now->copy();

        if ($isOpen) {
            $nextClosing->setTime(15, 30, 0);
        } else {
            if ($now->hour >= 15 || ($now->hour == 15 && $now->minute >= 30)) {
                $nextOpening->addDay();
            }
            while ($nextOpening->isWeekend()) {
                $nextOpening->addDay();
            }
            $nextOpening->setTime(9, 15, 0);
        }

        return new MarketStatusDTO(
            exchange: $exchange,
            status: $status,
            isOpen: $isOpen,
            nextOpening: new Timestamp($nextOpening),
            nextClosing: $isOpen ? new Timestamp($nextClosing) : null
        );
    }

    public function getCorporateActions(Ticker $ticker): array
    {
        $len = strlen($ticker->symbol);
        $executionDate = Carbon::now('Asia/Kolkata')->subDays($len);

        return [
            new CorporateAction(
                ticker: $ticker,
                type: 'dividend',
                executionDate: new Timestamp($executionDate),
                details: 'Interim Dividend of Rs. '.($len % 10 + 1).'.00 per share',
                amount: new Price(($len % 10 + 1) * 100)
            ),
        ];
    }

    private function determineMarketStatus(Exchange $exchange): MarketStatus
    {
        $now = Carbon::now('Asia/Kolkata');

        if ($now->isWeekend()) {
            return MarketStatus::Closed;
        }

        $time = $now->format('H:i');

        if ($time < '09:00') {
            return MarketStatus::Closed;
        }

        if ($time >= '09:00' && $time < '09:15') {
            return MarketStatus::PreMarket;
        }

        if ($time >= '09:15' && $time < '15:30') {
            return MarketStatus::Open;
        }

        if ($time >= '15:30' && $time < '16:00') {
            return MarketStatus::PostMarket;
        }

        return MarketStatus::Closed;
    }
}
