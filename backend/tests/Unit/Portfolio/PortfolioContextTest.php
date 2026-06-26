<?php

declare(strict_types=1);

namespace Tests\Unit\Portfolio;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Holding;
use App\Models\Stock;
use App\Models\Order;
use App\Models\Trade;
use App\Models\StockPrice;
use App\Enums\OrderSide;
use App\Enums\MarketStatus;
use App\Portfolio\Actions\LoadPortfolioContextAction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group portfolio
 * @group context
 */
final class PortfolioContextTest extends TestCase
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

        // Configure provider
        config(['market_data.provider' => 'MockProvider']);

        $this->user = User::query()->create([
            'name' => 'John Doe',
            'email' => 'john.doe2@example.com',
            'password' => bcrypt('secret'),
            'referral_code' => 'JOHNDOE456',
            'status' => 'active',
        ]);

        $this->wallet = Wallet::query()->create([
            'user_id' => $this->user->id,
            'virtual_cash_paise' => 100000000,
            'coin_balance' => 0,
            'total_deposited_paise' => 100000000,
            'total_withdrawn_paise' => 0,
        ]);

        $this->stock = Stock::query()->create([
            'symbol' => 'RELIANCE',
            'name' => 'Reliance Industries',
            'exchange' => 'NSE',
            'isin' => 'INE002A01018',
            'sector' => 'Energy',
            'is_active' => true,
            'is_tradeable' => true,
        ]);
    }

    public function test_load_portfolio_context_action(): void
    {
        $holding = Holding::query()->create([
            'user_id' => $this->user->id,
            'stock_id' => $this->stock->id,
            'symbol' => 'RELIANCE',
            'quantity' => 10,
            'average_buy_price_paise' => 200000,
            'total_invested_paise' => 2000000,
            'current_value_paise' => 2000000,
        ]);

        $order = Order::query()->create([
            'user_id' => $this->user->id,
            'stock_id' => $this->stock->id,
            'symbol' => 'RELIANCE',
            'idempotency_key' => 'idemp-1',
            'side' => OrderSide::Buy,
            'order_type' => 'limit',
            'status' => 'pending',
            'quantity' => 5,
            'limit_price_paise' => 195000,
        ]);

        $trade = Trade::query()->create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'stock_id' => $this->stock->id,
            'symbol' => 'RELIANCE',
            'side' => OrderSide::Buy,
            'quantity' => 5,
            'price_paise' => 195000,
            'total_value_paise' => 975000,
            'net_value_paise' => 975000,
            'executed_at' => Carbon::now(),
        ]);

        // Create price in DB
        StockPrice::query()->create([
            'stock_id' => $this->stock->id,
            'symbol' => $this->stock->symbol,
            'ltp_paise' => 210000,
            'open_paise' => 200000,
            'high_paise' => 220000,
            'low_paise' => 199000,
            'close_paise' => 200000,
            'change_paise' => 10000,
            'change_percent' => 5.0,
            'volume' => 50000,
            'market_status' => MarketStatus::Open,
            'quoted_at' => Carbon::now(),
        ]);

        // Resolve and execute LoadPortfolioContextAction
        $action = $this->app->make(LoadPortfolioContextAction::class);
        $context = $action->execute($this->user->id);

        $this->assertEquals($this->user->id, $context->userId());
        $this->assertEquals(100000000, $context->cashPaise());
        $this->assertCount(1, $context->holdings);
        $this->assertTrue(isset($context->holdings[$this->stock->id]));
        $this->assertEquals(10, $context->getHolding($this->stock->id)->quantity);
        $this->assertNotNull($context->getQuote('RELIANCE'));
        $actualLtp = $context->getQuote('RELIANCE')->ltp->valuePaise;
        $this->assertTrue(abs($actualLtp - 210000) <= 210000 * 0.02);
        $this->assertCount(1, $context->openOrders);
        $this->assertCount(1, $context->trades);
    }
}
