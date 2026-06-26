<?php

declare(strict_types=1);

namespace Tests\Unit\Trading;

use App\Enums\MarketStatus;
use App\Enums\OrderSide;
use App\Models\Holding;
use App\Models\Order;
use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\Trade;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\Wallet;
use App\Trading\Contracts\TradingEngineContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\OrderStatus;
use App\Trading\Enums\OrderType;
use Carbon\Carbon;
use Database\Seeders\FeatureFlagsSeeder;
use Database\Seeders\GameRulesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group trading
 * @group integration
 */
final class TradingEngineTest extends TestCase
{
    use RefreshDatabase;

    private TradingEngineContract $tradingEngine;
    private User $user;
    private Stock $stock;
    private StockPrice $stockPrice;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed database rules & feature flags
        (new GameRulesSeeder)->run();
        (new FeatureFlagsSeeder)->run();

        // 2. Set active provider to MockProvider
        config(['market_data.provider' => 'MockProvider']);

        // Resolve engine
        $this->tradingEngine = $this->app->make(TradingEngineContract::class);

        // 3. Create test user, wallet, level, and stock
        $this->user = User::query()->create([
            'name' => 'Test Trader',
            'email' => 'trader@example.com',
            'password' => bcrypt('password'),
            'referral_code' => 'TRADE123',
            'status' => 'active',
            'is_premium' => false,
        ]);

        Wallet::query()->create([
            'user_id' => $this->user->id,
            'virtual_cash_paise' => 100000000, // ₹10,00,000 = 100,000,000 paise
            'coin_balance' => 0,
            'total_deposited_paise' => 100000000,
            'total_withdrawn_paise' => 0,
        ]);

        UserLevel::query()->create([
            'user_id' => $this->user->id,
            'current_level' => 1,
            'current_xp' => 0,
            'next_level_xp' => 1000,
        ]);

        $this->stock = Stock::query()->create([
            'symbol' => 'RELIANCE',
            'name' => 'Reliance Industries Ltd.',
            'exchange' => 'NSE',
            'isin' => 'INE002A01018',
            'is_active' => true,
            'is_tradeable' => true,
        ]);

        // Create initial stock price in the database
        $this->stockPrice = StockPrice::query()->create([
            'stock_id' => $this->stock->id,
            'symbol' => $this->stock->symbol,
            'ltp_paise' => 250000, // ₹2500.00
            'open_paise' => 250000,
            'high_paise' => 260000,
            'low_paise' => 240000,
            'close_paise' => 250000,
            'change_paise' => 0,
            'change_percent' => 0.0,
            'volume' => 1000000,
            'market_status' => MarketStatus::Open,
            'quoted_at' => now(),
        ]);

        // Default time: Friday, 10:00 AM IST (market is open)
        Carbon::setTestNow(Carbon::create(2026, 6, 26, 10, 0, 0, 'Asia/Kolkata'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset test time
        parent::tearDown();
    }

    public function test_market_buy_executes_immediately_updating_wallet_and_holdings(): void
    {
        $request = new TradeRequest(
            userId: $this->user->id,
            stockId: $this->stock->id,
            symbol: $this->stock->symbol,
            side: OrderSide::Buy,
            type: OrderType::Market,
            quantity: 10,
            idempotencyKey: 'test_market_buy_'.uniqid(),
        );

        $result = $this->tradingEngine->execute($request);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(OrderStatus::Filled, $result->status);
        $this->assertEquals(10, $result->filledQuantity);
        $this->assertGreaterThan(0, $result->averageFillPricePaise);

        // Verify Wallet cash decremented
        $wallet = Wallet::query()->where('user_id', $this->user->id)->first();
        $this->assertLessThan(100000000, $wallet->virtual_cash_paise);

        // Verify Holding updated/created
        $holding = Holding::query()->where('user_id', $this->user->id)->where('stock_id', $this->stock->id)->first();
        $this->assertNotNull($holding);
        $this->assertEquals(10, $holding->quantity);

        // Verify Order is persisted as filled
        $order = Order::query()->find($result->orderId);
        $this->assertNotNull($order);
        $this->assertEquals('filled', $order->status);

        // Verify Trade is persisted
        $trade = Trade::query()->find($result->tradeId);
        $this->assertNotNull($trade);
        $this->assertEquals(10, $trade->quantity);
    }

    public function test_limit_buy_below_market_price_remains_open(): void
    {
        // Limit price set to ₹100.00 (10000 paise), far below RELIANCE price
        $request = new TradeRequest(
            userId: $this->user->id,
            stockId: $this->stock->id,
            symbol: $this->stock->symbol,
            side: OrderSide::Buy,
            type: OrderType::Limit,
            quantity: 5,
            idempotencyKey: 'test_limit_buy_open_'.uniqid(),
            limitPricePaise: 10000,
        );

        $result = $this->tradingEngine->execute($request);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(OrderStatus::Open, $result->status);
        $this->assertEquals(0, $result->filledQuantity);

        // Verify Wallet and Holdings are untouched
        $wallet = Wallet::query()->where('user_id', $this->user->id)->first();
        $this->assertEquals(100000000, $wallet->virtual_cash_paise);

        $holding = Holding::query()->where('user_id', $this->user->id)->where('stock_id', $this->stock->id)->first();
        $this->assertNull($holding);
    }

    public function test_market_sell_fails_if_insufficient_holdings(): void
    {
        $request = new TradeRequest(
            userId: $this->user->id,
            stockId: $this->stock->id,
            symbol: $this->stock->symbol,
            side: OrderSide::Sell,
            type: OrderType::Market,
            quantity: 5,
            idempotencyKey: 'test_sell_insufficient_'.uniqid(),
        );

        $result = $this->tradingEngine->execute($request);

        $this->assertEquals(OrderStatus::Rejected, $result->status);
        $this->assertStringContainsString('Insufficient stock holdings', $result->failureReason);
    }

    public function test_market_buy_fails_if_insufficient_funds(): void
    {
        $request = new TradeRequest(
            userId: $this->user->id,
            stockId: $this->stock->id,
            symbol: $this->stock->symbol,
            side: OrderSide::Buy,
            type: OrderType::Market,
            quantity: 1000, // 1000 * ~₹2500 = ₹25,00,000
            idempotencyKey: 'test_buy_insufficient_'.uniqid(),
        );

        $result = $this->tradingEngine->execute($request);

        $this->assertEquals(OrderStatus::Rejected, $result->status);
        $this->assertStringContainsString('Insufficient funds', $result->failureReason);
    }

    public function test_bracket_order_places_both_child_legs(): void
    {
        // Turn on premium for user
        $this->user->update(['is_premium' => true]);

        $request = new TradeRequest(
            userId: $this->user->id,
            stockId: $this->stock->id,
            symbol: $this->stock->symbol,
            side: OrderSide::Buy,
            type: OrderType::Bracket,
            quantity: 5,
            idempotencyKey: 'test_bracket_'.uniqid(),
            limitPricePaise: 260000,
            stopPricePaise: 240000,
        );

        $result = $this->tradingEngine->execute($request);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(OrderStatus::Filled, $result->status);

        // Verify child limit and stop legs exist in the database with "open" status
        $limitLeg = Order::query()
            ->where('idempotency_key', $request->idempotencyKey.'-tp')
            ->first();

        $stopLeg = Order::query()
            ->where('idempotency_key', $request->idempotencyKey.'-sl')
            ->first();

        $this->assertNotNull($limitLeg);
        $this->assertEquals('open', $limitLeg->status);
        $this->assertEquals(260000, $limitLeg->limit_price_paise);
        $this->assertEquals(OrderSide::Sell, $limitLeg->side);

        $this->assertNotNull($stopLeg);
        $this->assertEquals('open', $stopLeg->status);
        $this->assertEquals(240000, $stopLeg->stop_price_paise);
        $this->assertEquals(OrderSide::Sell, $stopLeg->side);
    }

    public function test_duplicate_order_rejected(): void
    {
        $key = 'test_dup_'.uniqid();
        $request = new TradeRequest(
            userId: $this->user->id,
            stockId: $this->stock->id,
            symbol: $this->stock->symbol,
            side: OrderSide::Buy,
            type: OrderType::Market,
            quantity: 1,
            idempotencyKey: $key,
        );

        // Place once
        $this->tradingEngine->execute($request);

        // Place twice with same key
        $result = $this->tradingEngine->execute($request);

        $this->assertEquals(OrderStatus::Rejected, $result->status);
        $this->assertStringContainsString('already exists', $result->failureReason);
    }

    public function test_non_premium_bracket_order_rejected(): void
    {
        $request = new TradeRequest(
            userId: $this->user->id,
            stockId: $this->stock->id,
            symbol: $this->stock->symbol,
            side: OrderSide::Buy,
            type: OrderType::Bracket,
            quantity: 5,
            idempotencyKey: 'test_non_premium_bracket_'.uniqid(),
            limitPricePaise: 260000,
            stopPricePaise: 240000,
        );

        $result = $this->tradingEngine->execute($request);

        $this->assertEquals(OrderStatus::Rejected, $result->status);
        $this->assertStringContainsString('premium-only feature', $result->failureReason);
    }

    public function test_outside_market_hours_rejected(): void
    {
        // Set time to Friday 8:00 PM IST (market closed)
        Carbon::setTestNow(Carbon::create(2026, 6, 26, 20, 0, 0, 'Asia/Kolkata'));

        $request = new TradeRequest(
            userId: $this->user->id,
            stockId: $this->stock->id,
            symbol: $this->stock->symbol,
            side: OrderSide::Buy,
            type: OrderType::Market,
            quantity: 1,
            idempotencyKey: 'test_closed_'.uniqid(),
        );

        $result = $this->tradingEngine->execute($request);

        $this->assertEquals(OrderStatus::Rejected, $result->status);
        $this->assertStringContainsString('closed', $result->failureReason);
    }
}
