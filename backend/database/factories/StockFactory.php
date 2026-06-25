<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

final class StockFactory extends Factory
{
    protected $model = Stock::class;

    /** Real NSE large-cap symbols for realistic data */
    private const NSE_SYMBOLS = [
        'RELIANCE', 'TCS', 'INFY', 'HDFCBANK', 'ICICIBANK', 'HINDUNILVR',
        'ITC', 'SBIN', 'BHARTIARTL', 'KOTAKBANK', 'LT', 'ASIANPAINT',
        'AXISBANK', 'MARUTI', 'SUNPHARMA', 'TITAN', 'BAJFINANCE', 'WIPRO',
        'HCLTECH', 'ULTRACEMCO', 'NESTLEIND', 'POWERGRID', 'NTPC', 'ONGC',
    ];

    private const SECTORS = [
        'Information Technology', 'Financial Services', 'Consumer Goods',
        'Energy', 'Healthcare', 'Industrials', 'Automobiles', 'Telecom',
        'Cement', 'Chemicals',
    ];

    public function definition(): array
    {
        static $symbolIndex = 0;
        $symbol = self::NSE_SYMBOLS[$symbolIndex % count(self::NSE_SYMBOLS)] . '_' . $symbolIndex;
        $symbolIndex++;

        return [
            'symbol'         => $this->faker->unique()->bothify('??###'),
            'name'           => $this->faker->company() . ' Ltd.',
            'exchange'       => 'NSE',
            'isin'           => 'INE' . $this->faker->bothify('??????##'),
            'sector'         => $this->faker->randomElement(self::SECTORS),
            'is_active'      => true,
            'is_nifty50'     => false,
            'is_sensex'      => false,
            'is_tradeable'   => true,
            'market_cap_paise' => $this->faker->numberBetween(10_00_00_00_000, 1000_00_00_00_000),
        ];
    }

    public function nifty50(): static
    {
        return $this->state(['is_nifty50' => true, 'is_sensex' => true]);
    }

    public function realSymbol(string $symbol, string $name): static
    {
        return $this->state(['symbol' => $symbol, 'name' => $name]);
    }
}
