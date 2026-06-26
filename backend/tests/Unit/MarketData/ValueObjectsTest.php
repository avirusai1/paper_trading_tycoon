<?php

declare(strict_types=1);

namespace Tests\Unit\MarketData;

use App\MarketData\ValueObjects\Currency;
use App\MarketData\ValueObjects\Exchange;
use App\MarketData\ValueObjects\Percentage;
use App\MarketData\ValueObjects\Price;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;
use App\MarketData\ValueObjects\Volume;
use Carbon\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class ValueObjectsTest extends TestCase
{
    /** @test */
    public function test_price_value_object(): void
    {
        $price = new Price(10050);
        $this->assertEquals(10050, $price->valuePaise);
        $this->assertEquals('100.50', $price->toRupees());

        $fromRupees = Price::fromRupees('100.50');
        $this->assertTrue($price->equals($fromRupees));

        $added = $price->add(new Price(5000));
        $this->assertEquals(15050, $added->valuePaise);

        $subtracted = $price->subtract(new Price(5000));
        $this->assertEquals(5050, $subtracted->valuePaise);

        $multiplied = $price->multiply(3);
        $this->assertEquals(30150, $multiplied->valuePaise);
    }

    /** @test */
    public function test_price_cannot_be_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Price(-1);
    }

    /** @test */
    public function test_percentage_value_object(): void
    {
        $percent = new Percentage(1.234);
        $this->assertEquals('1.23%', $percent->format());

        $other = new Percentage(1.2340001);
        $this->assertTrue($percent->equals($other));
    }

    /** @test */
    public function test_volume_value_object(): void
    {
        $volume = new Volume(1000);
        $this->assertEquals(1000, $volume->value);
    }

    /** @test */
    public function test_volume_cannot_be_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Volume(-10);
    }

    /** @test */
    public function test_currency_value_object(): void
    {
        $currency = new Currency('INR');
        $this->assertEquals('INR', $currency->code);

        $this->assertTrue($currency->equals(new Currency('inr')));
    }

    /** @test */
    public function test_currency_must_be_3_chars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Currency('USDT');
    }

    /** @test */
    public function test_exchange_value_object(): void
    {
        $exchange = new Exchange('nse');
        $this->assertEquals('NSE', $exchange->value);

        $this->expectException(InvalidArgumentException::class);
        new Exchange('NASDAQ');
    }

    /** @test */
    public function test_ticker_value_object(): void
    {
        $ticker = new Ticker('reliance');
        $this->assertEquals('RELIANCE', $ticker->symbol);

        $this->expectException(InvalidArgumentException::class);
        new Ticker('');
    }

    /** @test */
    public function test_timestamp_value_object(): void
    {
        $carbon = Carbon::parse('2026-06-26 18:00:00');
        $ts = new Timestamp($carbon);
        $this->assertEquals('2026-06-26 18:00:00', $ts->format());

        $tsFromStr = new Timestamp('2026-06-26 18:00:00');
        $this->assertTrue($ts->equals($tsFromStr));
    }
}
