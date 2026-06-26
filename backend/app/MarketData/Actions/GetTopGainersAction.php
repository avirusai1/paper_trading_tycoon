<?php

declare(strict_types=1);

namespace App\MarketData\Actions;

use App\Enums\MarketStatus;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\ValueObjects\Percentage;
use App\MarketData\ValueObjects\Price;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;
use App\MarketData\ValueObjects\Volume;
use App\Models\StockPrice;

final class GetTopGainersAction
{
    /**
     * @return StockQuote[]
     */
    public function execute(int $limit = 5): array
    {
        $prices = StockPrice::whereHas('stock', function ($q) {
            $q->tradeable();
        })
            ->orderBy('change_percent', 'desc')
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
}
