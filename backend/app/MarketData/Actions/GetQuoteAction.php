<?php

declare(strict_types=1);

namespace App\MarketData\Actions;

use App\MarketData\Cache\MarketDataCache;
use App\MarketData\Contracts\QuoteProviderContract;
use App\MarketData\Contracts\StockRepositoryContract;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\Events\QuoteUpdated;
use App\MarketData\Support\ProviderCoordinator;
use App\MarketData\Validators\TickerValidator;
use App\MarketData\ValueObjects\Ticker;

final class GetQuoteAction
{
    public function __construct(
        private MarketDataCache $cache,
        private ProviderCoordinator $coordinator,
        private StockRepositoryContract $repository
    ) {}

    public function execute(Ticker $ticker): StockQuote
    {
        TickerValidator::validate($ticker->symbol);

        $fallback = function () use ($ticker) {
            $quote = $this->coordinator->execute(
                QuoteProviderContract::class,
                fn (QuoteProviderContract $provider) => $provider->getQuote($ticker)
            );

            $this->repository->saveQuote($quote);

            event(new QuoteUpdated($quote));

            return $quote;
        };

        return $this->cache->getQuote($ticker, $fallback);
    }
}
