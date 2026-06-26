<?php

declare(strict_types=1);

namespace App\MarketData\Cache\Jobs;

use App\MarketData\Cache\MarketDataCache;
use App\MarketData\Contracts\QuoteProviderContract;
use App\MarketData\Contracts\StockRepositoryContract;
use App\MarketData\Support\ProviderCoordinator;
use App\MarketData\ValueObjects\Ticker;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

final class RevalidateMarketDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Ticker $ticker) {}

    public function handle(
        ProviderCoordinator $coordinator,
        MarketDataCache $cache,
        StockRepositoryContract $repository
    ): void {
        $symbol = $this->ticker->symbol;
        $jobLockKey = "lock:market_data:revalidate:{$symbol}";

        try {
            $quote = $coordinator->execute(QuoteProviderContract::class, function (QuoteProviderContract $provider) {
                return $provider->getQuote($this->ticker);
            });

            $cache->putQuoteInCache($this->ticker, $quote);
            $repository->saveQuote($quote);

        } catch (Exception $e) {
            logger()->error("RevalidateMarketDataJob failed for {$symbol}: {$e->getMessage()}", [
                'exception' => $e,
            ]);
        } finally {
            Cache::lock($jobLockKey)->forceRelease();
        }
    }
}
