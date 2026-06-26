<?php

declare(strict_types=1);

namespace App\MarketData\Cache;

use App\MarketData\Cache\Jobs\RevalidateMarketDataJob;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\ValueObjects\Ticker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class MarketDataCache
{
    /** @var array<string, StockQuote> */
    private array $inMemoryQuotes = [];

    private int $freshTtl;
    private int $staleTtl;

    public function __construct()
    {
        $this->freshTtl = (int) config('market_data.cache.fresh_ttl', 5);
        $this->staleTtl = (int) config('market_data.cache.stale_ttl', 300);
    }

    /**
     * Get quote from cache, or invoke the fallback to fetch it.
     * Uses SWR (Stale-While-Revalidate) and stampede locks.
     *
     * @param  callable(): StockQuote  $fallback
     */
    public function getQuote(Ticker $ticker, callable $fallback): StockQuote
    {
        $symbol = $ticker->symbol;

        if (isset($this->inMemoryQuotes[$symbol])) {
            return $this->inMemoryQuotes[$symbol];
        }

        $cacheKey = $this->getCacheKey($symbol);
        $cachedEntry = Cache::get($cacheKey);

        $now = time();

        if ($cachedEntry && is_array($cachedEntry)) {
            $quote = $cachedEntry['data'] ?? null;
            $expiresAt = (int) ($cachedEntry['expires_at'] ?? 0);
            $hardExpiresAt = (int) ($cachedEntry['hard_expires_at'] ?? 0);

            if ($quote instanceof StockQuote) {
                if ($now < $expiresAt) {
                    $this->inMemoryQuotes[$symbol] = $quote;

                    return $quote;
                }

                if ($now >= $expiresAt && $now < $hardExpiresAt) {
                    $this->triggerRevalidation($ticker);
                    $this->inMemoryQuotes[$symbol] = $quote;

                    return $quote;
                }
            }
        }

        $lockKey = "lock:market_data:quote:{$symbol}";
        $lock = Cache::lock($lockKey, 10);

        try {
            $quote = $lock->block(5, function () use ($fallback) {
                return $fallback();
            });

            $this->putQuoteInCache($ticker, $quote);
            $this->inMemoryQuotes[$symbol] = $quote;

            return $quote;

        } catch (\Exception $e) {
            if ($cachedEntry && isset($cachedEntry['data']) && $cachedEntry['data'] instanceof StockQuote) {
                Log::warning("MarketDataCache: Synchronous fetch failed for {$symbol}. Returning stale quote.", [
                    'exception' => $e,
                ]);

                return $cachedEntry['data'];
            }

            throw $e;
        }
    }

    public function putQuoteInCache(Ticker $ticker, StockQuote $quote): void
    {
        $symbol = $ticker->symbol;
        $now = time();

        $entry = [
            'data' => $quote,
            'expires_at' => $now + $this->freshTtl,
            'hard_expires_at' => $now + $this->staleTtl,
        ];

        Cache::put($this->getCacheKey($symbol), $entry, $this->staleTtl);
    }

    public function clearInMemory(): void
    {
        $this->inMemoryQuotes = [];
    }

    private function getCacheKey(string $symbol): string
    {
        return "market_data:quote:{$symbol}";
    }

    private function triggerRevalidation(Ticker $ticker): void
    {
        $symbol = $ticker->symbol;
        $jobLockKey = "lock:market_data:revalidate:{$symbol}";

        $lock = Cache::lock($jobLockKey, 30);
        if ($lock->get()) {
            RevalidateMarketDataJob::dispatch($ticker);
        }
    }
}
