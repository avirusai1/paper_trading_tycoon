<?php

declare(strict_types=1);

namespace Tests\Unit\MarketData;

use App\MarketData\Services\MarketDataService;
use App\MarketData\ValueObjects\Ticker;
use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketDataServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_service_facade_resolves_and_runs(): void
    {
        $stock = Stock::create([
            'symbol' => 'TCS',
            'name' => 'Tata Consultancy Services',
            'exchange' => 'NSE',
            'is_active' => true,
            'is_tradeable' => true,
        ]);

        StockPrice::create([
            'stock_id' => $stock->id,
            'symbol' => 'TCS',
            'ltp_paise' => 389500,
            'open_paise' => 389000,
            'high_paise' => 390000,
            'low_paise' => 388000,
            'close_paise' => 389000,
            'change_paise' => 500,
            'change_percent' => 0.13,
            'volume' => 2000000,
            'market_status' => 'closed',
            'quoted_at' => now(),
        ]);

        config(['market_data.provider' => 'mock']);

        $service = app(MarketDataService::class);
        $quote = $service->getQuote(new Ticker('TCS'));

        $this->assertEquals('TCS', $quote->ticker->symbol);
        $this->assertGreaterThan(0, $quote->ltp->valuePaise);
    }
}
