<?php

declare(strict_types=1);

namespace Tests\Unit\Portfolio;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Holding;
use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\PortfolioSnapshot;
use App\Enums\MarketStatus;
use App\Portfolio\Contracts\PortfolioServiceContract;
use App\Portfolio\DTOs\PortfolioResult;
use App\Portfolio\Events\PortfolioCalculated;
use App\Portfolio\Events\SnapshotGenerated;
use App\Events\PortfolioUpdated as GlobalPortfolioUpdated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group portfolio
 * @group pipeline
 */
final class PipelineTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Wallet $wallet;
    private Stock $stock;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed feature flags & game rules
        (new \Database\Seeders\FeatureFlagsSeeder)->run();
        (new \Database\Seeders\GameRulesSeeder)->run();

        // Use mock provider to resolve prices from stock_prices table
        config(['market_data.provider' => 'MockProvider']);

        $this->user = User::query()->create([
            'name' => 'John Doe',
            'email' => 'john.doe3@example.com',
            'password' => bcrypt('secret'),
            'referral_code' => 'JOHNDOE789',
            'status' => 'active',
        ]);

        $this->wallet = Wallet::query()->create([
            'user_id' => $this->user->id,
            'virtual_cash_paise' => 100000000, // ₹10,00,000 cash
            'coin_balance' => 0,
            'total_deposited_paise' => 100000000,
            'total_withdrawn_paise' => 0,
        ]);

        $this->stock = Stock::query()->create([
            'symbol' => 'TCS',
            'name' => 'Tata Consultancy Services',
            'exchange' => 'NSE',
            'isin' => 'INE467B01029',
            'sector' => 'Technology',
            'is_active' => true,
            'is_tradeable' => true,
        ]);
    }

    public function test_portfolio_refresh_pipeline(): void
    {
        Event::fake([
            GlobalPortfolioUpdated::class,
            PortfolioCalculated::class,
            SnapshotGenerated::class,
        ]);

        Holding::query()->create([
            'user_id' => $this->user->id,
            'stock_id' => $this->stock->id,
            'symbol' => 'TCS',
            'quantity' => 10,
            'average_buy_price_paise' => 300000, // ₹3,000 per share (₹30,000 total invested)
            'total_invested_paise' => 3000000,
            'current_value_paise' => 3000000,
        ]);

        // Create stock price in database (read by MockProvider)
        StockPrice::query()->create([
            'stock_id' => $this->stock->id,
            'symbol' => $this->stock->symbol,
            'ltp_paise' => 320000, // ₹3,200
            'open_paise' => 310000,
            'high_paise' => 335000,
            'low_paise' => 308000,
            'close_paise' => 300000,
            'change_paise' => 20000,
            'change_percent' => 6.67,
            'volume' => 25000,
            'market_status' => MarketStatus::Open,
            'quoted_at' => Carbon::now(),
        ]);

        // Resolve service facade
        $service = $this->app->make(PortfolioServiceContract::class);

        // Execute refresh
        $result = $service->refresh($this->user->id);

        // Verify result DTO
        $this->assertInstanceOf(PortfolioResult::class, $result);
        $this->assertEquals($this->user->id, $result->userId);
        
        $expectedHoldingValue = $result->holdingValue->valuePaise;
        $expectedNetWorth = 100000000 + $expectedHoldingValue;
        $this->assertEquals($expectedNetWorth, $result->netWorth->valuePaise);
        $this->assertEquals(100000000, $result->cashValue->valuePaise);

        $expectedAbsoluteReturn = $expectedNetWorth - 100000000;
        $this->assertEquals($expectedAbsoluteReturn, $result->absoluteReturn->absolutePaise);

        // Verify database snapshot was written
        $snapshot = PortfolioSnapshot::query()
            ->where('user_id', $this->user->id)
            ->latest('taken_at')
            ->first();

        $this->assertNotNull($snapshot);
        $this->assertEquals(100000000, $snapshot->virtual_cash_paise);
        $this->assertEquals($expectedHoldingValue, $snapshot->holdings_value_paise);
        $this->assertEquals($expectedNetWorth, $snapshot->total_portfolio_value_paise);
        $this->assertEquals($expectedAbsoluteReturn, $snapshot->total_pnl_paise);

        // Verify Cache entry
        $cached = Cache::get('portfolio_result_' . $this->user->id);
        $this->assertNotNull($cached);
        $this->assertEquals($result->netWorth->valuePaise, $cached->netWorth->valuePaise);

        // Verify event dispatches
        Event::assertDispatched(GlobalPortfolioUpdated::class);
        Event::assertDispatched(PortfolioCalculated::class);
        Event::assertDispatched(SnapshotGenerated::class);
    }
}
