<?php

declare(strict_types=1);

namespace Tests\Unit\Portfolio;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Holding;
use App\Models\Stock;
use App\Models\Trade;
use App\Enums\OrderSide;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\DTOs\MarketStatus as DtoMarketStatus;
use App\MarketData\ValueObjects\Price;
use App\MarketData\ValueObjects\Percentage;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Volume;
use App\MarketData\ValueObjects\Timestamp;
use App\Enums\MarketStatus;
use App\Portfolio\Calculators\ValuationCalculator;
use App\Portfolio\Calculators\ReturnCalculator;
use App\Portfolio\Calculators\AnalyticsCalculator;
use App\Portfolio\Calculators\RiskCalculator;
use App\Portfolio\Contexts\PortfolioContext;
use App\Models\PortfolioSnapshot;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group portfolio
 * @group calculators
 */
final class CalculatorsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Wallet $wallet;
    private Stock $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::query()->create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => bcrypt('secret'),
            'referral_code' => 'JOHNDOE123',
            'status' => 'active',
        ]);

        $this->wallet = Wallet::query()->create([
            'user_id' => $this->user->id,
            'virtual_cash_paise' => 50000000, // ₹5,00,000
            'coin_balance' => 0,
            'total_deposited_paise' => 100000000, // ₹10,00,000 deposited
            'total_withdrawn_paise' => 50000000,
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

    public function test_valuation_calculator(): void
    {
        $holding = new Holding([
            'user_id' => $this->user->id,
            'stock_id' => $this->stock->id,
            'symbol' => 'RELIANCE',
            'quantity' => 100,
            'average_buy_price_paise' => 240000,
            'total_invested_paise' => 24000000,
            'current_value_paise' => 24000000,
        ]);
        $holding->stock = $this->stock;

        $quote = new StockQuote(
            ticker: new Ticker('RELIANCE'),
            ltp: new Price(250000), // ₹2,500
            open: new Price(245000),
            high: new Price(255000),
            low: new Price(244000),
            close: new Price(242000),
            change: new Price(8000),
            changePercent: new Percentage(3.3),
            volume: new Volume(100000),
            marketStatus: MarketStatus::Open,
            quotedAt: new Timestamp(Carbon::now())
        );

        $context = new PortfolioContext(
            user: $this->user,
            wallet: $this->wallet,
            holdings: [$this->stock->id => $holding],
            quotes: ['RELIANCE' => $quote],
            marketStatus: new DtoMarketStatus(new \App\MarketData\ValueObjects\Exchange('NSE'), MarketStatus::Open, true),
            latestSnapshot: null,
            openOrders: new Collection(),
            trades: new Collection(),
            currentLeague: null,
            currentSeason: null,
            featureFlags: [],
            builtAt: Carbon::now()
        );

        $calculator = new ValuationCalculator();

        $this->assertEquals(50000000, $calculator->cashValue($context)->valuePaise);
        $this->assertEquals(25000000, $calculator->holdingValue($context)->valuePaise); // 100 * 250000 paise = 25,000,000
        $this->assertEquals(75000000, $calculator->netWorth($context)->valuePaise); // 50,000,000 + 25,000,000
    }

    public function test_return_calculator(): void
    {
        $calculator = new ReturnCalculator();

        $holding = new Holding([
            'user_id' => $this->user->id,
            'stock_id' => $this->stock->id,
            'symbol' => 'RELIANCE',
            'quantity' => 10,
            'average_buy_price_paise' => 200000,
            'total_invested_paise' => 2000000,
            'current_value_paise' => 2000000,
        ]);
        $holding->stock = $this->stock;

        $quote = new StockQuote(
            ticker: new Ticker('RELIANCE'),
            ltp: new Price(220000),
            open: new Price(210000),
            high: new Price(230000),
            low: new Price(210000),
            close: new Price(210000),
            change: new Price(10000), // Today's change is +₹100 (10,000 paise)
            changePercent: new Percentage(4.7),
            volume: new Volume(50000),
            marketStatus: MarketStatus::Open,
            quotedAt: new Timestamp(Carbon::now())
        );

        $context = new PortfolioContext(
            user: $this->user,
            wallet: $this->wallet,
            holdings: [$this->stock->id => $holding],
            quotes: ['RELIANCE' => $quote],
            marketStatus: new DtoMarketStatus(new \App\MarketData\ValueObjects\Exchange('NSE'), MarketStatus::Open, true),
            latestSnapshot: null,
            openOrders: new Collection(),
            trades: new Collection(),
            currentLeague: null,
            currentSeason: null,
            featureFlags: [],
            builtAt: Carbon::now()
        );

        // Net worth: cash (50,000,000) + holding value (10 * 2,20,000 = 22,000,000) = 72,000,000 paise
        // Total invested/deposited: 100,000,000 paise
        // Absolute return: 72,000,000 - 100,000,000 = -28,000,000 paise
        // Percentage return: -28%
        $absReturn = $calculator->absoluteReturn($context, 72000000);
        $this->assertEquals(-28000000, $absReturn->absolutePaise);
        $this->assertEquals(-28.0, $absReturn->percentage);

        // Today's Profit: 10 * 10000 paise = 100,000 paise (₹1,000)
        // Yesterday's value: 72,000,000 - 100,000 = 71,900,000 paise
        // Today's return percent: 100,000 / 71,900,000 = ~0.139%
        $todayPnl = $calculator->todayProfitLoss($context, 72000000);
        $this->assertEquals(100000, $todayPnl->absolutePaise);
        $this->assertLessThan(0.14, $todayPnl->percentage);

        // CAGR checks
        $this->assertEquals(10.0, $calculator->compoundedReturn(10.0, 0.0)); // Return total return if years <= 0
        $this->assertNear(44.0, $calculator->compoundedReturn(107.36, 2.0)); // (2.0736)^(0.5) - 1 = 1.44 - 1 = 44%
    }

    public function test_analytics_calculator(): void
    {
        $trades = new Collection([
            new Trade([
                'id' => 1,
                'user_id' => $this->user->id,
                'stock_id' => $this->stock->id,
                'symbol' => 'RELIANCE',
                'side' => OrderSide::Buy,
                'quantity' => 10,
                'price_paise' => 200000, // ₹2,000
                'executed_at' => Carbon::now()->subDays(5),
            ]),
            new Trade([
                'id' => 2,
                'user_id' => $this->user->id,
                'stock_id' => $this->stock->id,
                'symbol' => 'RELIANCE',
                'side' => OrderSide::Sell,
                'quantity' => 5,
                'price_paise' => 220000, // ₹2,200 (profit of 5 * ₹200 = 100,000 paise)
                'executed_at' => Carbon::now()->subDays(2),
            ]),
        ]);

        $holding = new Holding([
            'user_id' => $this->user->id,
            'stock_id' => $this->stock->id,
            'symbol' => 'RELIANCE',
            'quantity' => 5,
            'average_buy_price_paise' => 200000,
            'total_invested_paise' => 1000000,
            'current_value_paise' => 1100000,
        ]);
        $holding->stock = $this->stock;

        $quote = new StockQuote(
            ticker: new Ticker('RELIANCE'),
            ltp: new Price(220000),
            open: new Price(210000),
            high: new Price(230000),
            low: new Price(210000),
            close: new Price(210000),
            change: new Price(10000),
            changePercent: new Percentage(4.7),
            volume: new Volume(50000),
            marketStatus: MarketStatus::Open,
            quotedAt: new Timestamp(Carbon::now())
        );

        $context = new PortfolioContext(
            user: $this->user,
            wallet: $this->wallet,
            holdings: [$this->stock->id => $holding],
            quotes: ['RELIANCE' => $quote],
            marketStatus: new DtoMarketStatus(new \App\MarketData\ValueObjects\Exchange('NSE'), MarketStatus::Open, true),
            latestSnapshot: null,
            openOrders: new Collection(),
            trades: $trades,
            currentLeague: null,
            currentSeason: null,
            featureFlags: [],
            builtAt: Carbon::now()
        );

        $calculator = new AnalyticsCalculator();
        $analytics = $calculator->calculate($context, 61000000, 1100000);

        $this->assertEquals(2, $analytics->totalTrades);
        $this->assertEquals(1, $analytics->winningTrades);
        $this->assertEquals(0, $analytics->losingTrades);
        $this->assertEquals(100.0, $analytics->winRate);
        $this->assertEquals('RELIANCE', $analytics->largestWinner['symbol']);
        $this->assertEquals(100000, $analytics->largestWinner['amount_paise']);
        $this->assertEquals('RELIANCE', $analytics->bestStock);
        $this->assertNear(10.0, $analytics->averageReturnPercent);
        $this->assertEquals(100.0, $analytics->portfolioConcentration);
    }

    public function test_risk_calculator(): void
    {
        $historicalSnapshots = new Collection([
            new PortfolioSnapshot([
                'user_id' => $this->user->id,
                'total_portfolio_value_paise' => 100000000,
                'taken_at' => Carbon::now()->subDays(3),
            ]),
            new PortfolioSnapshot([
                'user_id' => $this->user->id,
                'total_portfolio_value_paise' => 110000000, // peak
                'taken_at' => Carbon::now()->subDays(2),
            ]),
            new PortfolioSnapshot([
                'user_id' => $this->user->id,
                'total_portfolio_value_paise' => 99000000, // drawdown: (110 - 99)/110 = 10%
                'taken_at' => Carbon::now()->subDays(1),
            ]),
        ]);

        $holding = new Holding([
            'user_id' => $this->user->id,
            'stock_id' => $this->stock->id,
            'symbol' => 'RELIANCE',
            'quantity' => 10,
            'average_buy_price_paise' => 200000,
            'total_invested_paise' => 2000000,
            'current_value_paise' => 2200000,
        ]);
        $holding->stock = $this->stock;

        $quote = new StockQuote(
            ticker: new Ticker('RELIANCE'),
            ltp: new Price(220000),
            open: new Price(210000),
            high: new Price(230000),
            low: new Price(210000),
            close: new Price(210000),
            change: new Price(10000),
            changePercent: new Percentage(4.7),
            volume: new Volume(50000),
            marketStatus: MarketStatus::Open,
            quotedAt: new Timestamp(Carbon::now())
        );

        $context = new PortfolioContext(
            user: $this->user,
            wallet: $this->wallet,
            holdings: [$this->stock->id => $holding],
            quotes: ['RELIANCE' => $quote],
            marketStatus: new DtoMarketStatus(new \App\MarketData\ValueObjects\Exchange('NSE'), MarketStatus::Open, true),
            latestSnapshot: null,
            openOrders: new Collection(),
            trades: new Collection(),
            currentLeague: null,
            currentSeason: null,
            featureFlags: [],
            builtAt: Carbon::now()
        );

        $calculator = new RiskCalculator();
        $risk = $calculator->calculate($context, 52200000, 2200000, 100.0, $historicalSnapshots);

        $this->assertEquals(10.0, $risk->maxDrawdownPercent);
        $this->assertGreaterThan(0.0, $risk->volatility);
        $this->assertEquals('RELIANCE', $risk->largestPosition);
        $this->assertGreaterThan(0, $risk->riskScore);
        $this->assertGreaterThan(0, $risk->healthScore);
    }

    private function assertNear(float $expected, float $actual, float $delta = 0.01): void
    {
        $this->assertTrue(abs($expected - $actual) < $delta, "Expected {$expected} to be near {$actual} (delta: {$delta})");
    }
}
