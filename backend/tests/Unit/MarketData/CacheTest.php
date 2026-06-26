<?php

declare(strict_types=1);

namespace Tests\Unit\MarketData;

use App\Enums\MarketStatus;
use App\MarketData\Cache\Jobs\RevalidateMarketDataJob;
use App\MarketData\Cache\MarketDataCache;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\ValueObjects\Percentage;
use App\MarketData\ValueObjects\Price;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;
use App\MarketData\ValueObjects\Volume;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::clear();
    }

    /** @test */
    public function test_cache_miss_invokes_fallback_and_caches(): void
    {
        $cache = new MarketDataCache;
        $ticker = new Ticker('INFY');

        $quote = new StockQuote(
            ticker: $ticker,
            ltp: new Price(163000),
            open: new Price(162000),
            high: new Price(164000),
            low: new Price(161000),
            close: new Price(162000),
            change: new Price(1000),
            changePercent: new Percentage(0.62),
            volume: new Volume(1500000),
            marketStatus: MarketStatus::Open,
            quotedAt: Timestamp::now()
        );

        $fallbackCalled = 0;
        $fallback = function () use ($quote, &$fallbackCalled) {
            $fallbackCalled++;

            return $quote;
        };

        $result = $cache->getQuote($ticker, $fallback);
        $this->assertEquals(1, $fallbackCalled);
        $this->assertEquals($quote, $result);

        $result2 = $cache->getQuote($ticker, $fallback);
        $this->assertEquals(1, $fallbackCalled);
        $this->assertEquals($quote, $result2);
    }

    /** @test */
    public function test_stale_while_revalidate_dispatches_job(): void
    {
        Queue::fake();

        $cache = new MarketDataCache;
        $ticker = new Ticker('SBIN');

        $quote = new StockQuote(
            ticker: $ticker,
            ltp: new Price(82300),
            open: new Price(82000),
            high: new Price(83000),
            low: new Price(81800),
            close: new Price(82000),
            change: new Price(300),
            changePercent: new Percentage(0.36),
            volume: new Volume(2500000),
            marketStatus: MarketStatus::Open,
            quotedAt: Timestamp::now()
        );

        $now = time();
        $cacheKey = 'market_data:quote:SBIN';
        Cache::put($cacheKey, [
            'data' => $quote,
            'expires_at' => $now - 10,
            'hard_expires_at' => $now + 300,
        ], 300);

        $fallbackCalled = 0;
        $fallback = function () use ($quote, &$fallbackCalled) {
            $fallbackCalled++;

            return $quote;
        };

        $result = $cache->getQuote($ticker, $fallback);

        $this->assertEquals($quote, $result);
        $this->assertEquals(0, $fallbackCalled);

        Queue::assertPushed(RevalidateMarketDataJob::class, function ($job) use ($ticker) {
            return $job->ticker->symbol === $ticker->symbol;
        });
    }
}
