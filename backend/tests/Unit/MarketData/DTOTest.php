<?php

declare(strict_types=1);

namespace Tests\Unit\MarketData;

use App\Enums\MarketStatus;
use App\MarketData\DTOs\QuoteBatch;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\ValueObjects\Percentage;
use App\MarketData\ValueObjects\Price;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;
use App\MarketData\ValueObjects\Volume;
use Tests\TestCase;

class DTOTest extends TestCase
{
    /** @test */
    public function test_stock_quote_dto(): void
    {
        $ticker = new Ticker('RELIANCE');
        $ltp = new Price(294600);
        $open = new Price(294000);
        $high = new Price(296000);
        $low = new Price(293000);
        $close = new Price(294000);
        $change = new Price(600);
        $changePercent = new Percentage(0.20);
        $volume = new Volume(1000000);
        $status = MarketStatus::Open;
        $quotedAt = Timestamp::now();

        $quote = new StockQuote(
            ticker: $ticker,
            ltp: $ltp,
            open: $open,
            high: $high,
            low: $low,
            close: $close,
            change: $change,
            changePercent: $changePercent,
            volume: $volume,
            marketStatus: $status,
            quotedAt: $quotedAt
        );

        $this->assertEquals($ticker, $quote->ticker);
        $this->assertEquals($ltp, $quote->ltp);
        $this->assertEquals($status, $quote->marketStatus);
    }

    /** @test */
    public function test_quote_batch_dto(): void
    {
        $quote1 = new StockQuote(
            ticker: new Ticker('RELIANCE'),
            ltp: new Price(294600),
            open: new Price(294000),
            high: new Price(296000),
            low: new Price(293000),
            close: new Price(294000),
            change: new Price(600),
            changePercent: new Percentage(0.20),
            volume: new Volume(1000000),
            marketStatus: MarketStatus::Open,
            quotedAt: Timestamp::now()
        );

        $quote2 = new StockQuote(
            ticker: new Ticker('TCS'),
            ltp: new Price(389500),
            open: new Price(389000),
            high: new Price(390000),
            low: new Price(388000),
            close: new Price(389000),
            change: new Price(500),
            changePercent: new Percentage(0.13),
            volume: new Volume(2000000),
            marketStatus: MarketStatus::Open,
            quotedAt: Timestamp::now()
        );

        $batch = new QuoteBatch([$quote1, $quote2]);

        $this->assertTrue($batch->has('RELIANCE'));
        $this->assertTrue($batch->has(new Ticker('TCS')));
        $this->assertFalse($batch->has('INFY'));

        $this->assertEquals($quote1, $batch->get('RELIANCE'));
        $this->assertEquals($quote2, $batch->get(new Ticker('TCS')));
    }
}
