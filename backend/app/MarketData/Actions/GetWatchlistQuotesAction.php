<?php

declare(strict_types=1);

namespace App\MarketData\Actions;

use App\MarketData\DTOs\WatchlistQuote;
use App\MarketData\ValueObjects\CompanyName;
use App\MarketData\ValueObjects\Ticker;
use App\Models\Stock;

final class GetWatchlistQuotesAction
{
    public function __construct(private GetQuoteAction $getQuoteAction) {}

    /**
     * @param  Ticker[]  $tickers
     * @return WatchlistQuote[]
     */
    public function execute(array $tickers): array
    {
        $watchlistQuotes = [];

        foreach ($tickers as $ticker) {
            try {
                $quote = $this->getQuoteAction->execute($ticker);

                $stock = Stock::where('symbol', $ticker->symbol)->first();
                $name = $stock ? $stock->name : $ticker->symbol;

                $watchlistQuotes[] = new WatchlistQuote(
                    ticker: $ticker,
                    name: new CompanyName($name),
                    ltp: $quote->ltp,
                    change: $quote->change,
                    changePercent: $quote->changePercent
                );
            } catch (\Exception $e) {
                continue;
            }
        }

        return $watchlistQuotes;
    }
}
