<?php

declare(strict_types=1);

namespace App\MarketData\Repositories;

use App\Enums\MarketStatus;
use App\MarketData\Contracts\StockRepositoryContract;
use App\MarketData\DTOs\HistoricalBar;
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
use App\Models\StockDailyHistory;
use App\Models\StockPrice;
use Illuminate\Support\Facades\DB;

final class StockRepository implements StockRepositoryContract
{
    public function findStockByTicker(Ticker $ticker): ?Stock
    {
        return Stock::where('symbol', $ticker->symbol)->first();
    }

    public function getQuote(Ticker $ticker): ?StockQuote
    {
        $stock = $this->findStockByTicker($ticker);
        if (! $stock) {
            return null;
        }

        $stockPrice = StockPrice::where('stock_id', $stock->id)->first();
        if (! $stockPrice) {
            return null;
        }

        return new StockQuote(
            ticker: $ticker,
            ltp: new Price((int) $stockPrice->ltp_paise),
            open: new Price((int) $stockPrice->open_paise),
            high: new Price((int) $stockPrice->high_paise),
            low: new Price((int) $stockPrice->low_paise),
            close: new Price((int) $stockPrice->close_paise),
            change: new Price((int) abs($stockPrice->change_paise)),
            changePercent: new Percentage((float) $stockPrice->change_percent),
            volume: new Volume((int) $stockPrice->volume),
            marketStatus: $stockPrice->market_status instanceof MarketStatus ? $stockPrice->market_status : MarketStatus::from((string) $stockPrice->market_status),
            quotedAt: new Timestamp($stockPrice->quoted_at)
        );
    }

    public function saveQuote(StockQuote $quote): void
    {
        DB::transaction(function () use ($quote) {
            $stock = Stock::firstOrCreate(
                ['symbol' => $quote->ticker->symbol],
                [
                    'name' => $quote->ticker->symbol,
                    'exchange' => 'NSE',
                    'is_active' => true,
                    'is_tradeable' => true,
                ]
            );

            $changePaise = $quote->ltp->valuePaise - $quote->close->valuePaise;

            StockPrice::updateOrCreate(
                ['stock_id' => $stock->id],
                [
                    'symbol' => $stock->symbol,
                    'ltp_paise' => $quote->ltp->valuePaise,
                    'open_paise' => $quote->open->valuePaise,
                    'high_paise' => $quote->high->valuePaise,
                    'low_paise' => $quote->low->valuePaise,
                    'close_paise' => $quote->close->valuePaise,
                    'change_paise' => $changePaise,
                    'change_percent' => $quote->changePercent->value,
                    'volume' => $quote->volume->value,
                    'market_status' => $quote->marketStatus,
                    'quoted_at' => $quote->quotedAt->value,
                ]
            );
        });
    }

    public function saveQuotes(array $quotes): void
    {
        DB::transaction(function () use ($quotes) {
            foreach ($quotes as $quote) {
                $this->saveQuote($quote);
            }
        });
    }

    public function getHistoricalData(Ticker $ticker, Timestamp $startDate, Timestamp $endDate): array
    {
        $stock = $this->findStockByTicker($ticker);
        if (! $stock) {
            return [];
        }

        $records = StockDailyHistory::where('stock_id', $stock->id)
            ->whereBetween('trading_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('trading_date', 'asc')
            ->get();

        $bars = [];
        foreach ($records as $rec) {
            $bars[] = new HistoricalBar(
                ticker: $ticker,
                open: new Price((int) $rec->getAttribute('open_paise')),
                high: new Price((int) $rec->getAttribute('high_paise')),
                low: new Price((int) $rec->getAttribute('low_paise')),
                close: new Price((int) $rec->getAttribute('close_paise')),
                volume: new Volume((int) $rec->getAttribute('volume')),
                timestamp: new Timestamp($rec->getAttribute('trading_date'))
            );
        }

        return $bars;
    }

    public function saveHistoricalBars(array $bars): void
    {
        DB::transaction(function () use ($bars) {
            foreach ($bars as $bar) {
                $stock = $this->findStockByTicker($bar->ticker);
                if (! $stock) {
                    $stock = Stock::create([
                        'symbol' => $bar->ticker->symbol,
                        'name' => $bar->ticker->symbol,
                        'exchange' => 'NSE',
                        'is_active' => true,
                        'is_tradeable' => true,
                    ]);
                }

                StockDailyHistory::updateOrCreate(
                    [
                        'stock_id' => $stock->id,
                        'trading_date' => $bar->timestamp->format('Y-m-d'),
                    ],
                    [
                        'symbol' => $stock->symbol,
                        'open_paise' => $bar->open->valuePaise,
                        'high_paise' => $bar->high->valuePaise,
                        'low_paise' => $bar->low->valuePaise,
                        'close_paise' => $bar->close->valuePaise,
                        'volume' => $bar->volume->value,
                    ]
                );
            }
        });
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
}
