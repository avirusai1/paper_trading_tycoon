<?php

declare(strict_types=1);

namespace App\MarketData\Actions;

use App\Enums\MarketStatus;
use App\MarketData\DTOs\MarketSummary;
use App\MarketData\DTOs\SectorPerformance;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\ValueObjects\Percentage;
use App\MarketData\ValueObjects\Price;
use App\MarketData\ValueObjects\Sector;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;
use App\MarketData\ValueObjects\Volume;
use App\Models\StockPrice;
use Illuminate\Support\Facades\DB;

final class GetMarketSummaryAction
{
    public function execute(): MarketSummary
    {
        $gainers = $this->getQuotesSortedBy('change_percent', 'desc', 5);
        $losers = $this->getQuotesSortedBy('change_percent', 'asc', 5);
        $active = $this->getQuotesSortedBy('volume', 'desc', 5);
        $trending = $this->getQuotesSortedBy('ltp_paise', 'desc', 5);

        $sectorPerformances = $this->getSectorPerformances();

        return new MarketSummary(
            gainers: $gainers,
            losers: $losers,
            active: $active,
            trending: $trending,
            sectorPerformances: $sectorPerformances
        );
    }

    /**
     * @return StockQuote[]
     */
    private function getQuotesSortedBy(string $column, string $direction, int $limit): array
    {
        $prices = StockPrice::whereHas('stock', function ($q) {
            $q->tradeable();
        })
            ->orderBy($column, $direction)
            ->limit($limit)
            ->get();

        $quotes = [];
        foreach ($prices as $p) {
            $quotes[] = new StockQuote(
                ticker: new Ticker($p->symbol),
                ltp: new Price((int) $p->ltp_paise),
                open: new Price((int) $p->open_paise),
                high: new Price((int) $p->high_paise),
                low: new Price((int) $p->low_paise),
                close: new Price((int) $p->close_paise),
                change: new Price((int) abs($p->change_paise)),
                changePercent: new Percentage((float) $p->change_percent),
                volume: new Volume((int) $p->volume),
                marketStatus: $p->market_status instanceof MarketStatus ? $p->market_status : MarketStatus::from((string) $p->market_status),
                quotedAt: new Timestamp($p->quoted_at)
            );
        }

        return $quotes;
    }

    /**
     * @return SectorPerformance[]
     */
    private function getSectorPerformances(): array
    {
        $results = DB::table('stock_prices')
            ->join('stocks', 'stock_prices.stock_id', '=', 'stocks.id')
            ->select('stocks.sector', DB::raw('AVG(stock_prices.change_percent) as avg_change'))
            ->whereNotNull('stocks.sector')
            ->where('stocks.sector', '!=', '')
            ->where('stocks.is_active', true)
            ->groupBy('stocks.sector')
            ->orderByDesc('avg_change')
            ->get();

        $performances = [];
        foreach ($results as $res) {
            $performances[] = new SectorPerformance(
                sector: new Sector($res->sector),
                changePercent: new Percentage((float) $res->avg_change)
            );
        }

        return $performances;
    }
}
