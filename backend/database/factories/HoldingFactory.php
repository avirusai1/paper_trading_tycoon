<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Holding;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class HoldingFactory extends Factory
{
    protected $model = Holding::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 500);
        $avgBuy = $this->faker->numberBetween(10000, 500000);
        $currentLtp = (int) ($avgBuy * $this->faker->randomFloat(2, 0.7, 1.5));
        $invested = $quantity * $avgBuy;
        $current = $quantity * $currentLtp;

        return [
            'user_id' => User::factory(),
            'stock_id' => Stock::factory(),
            'symbol' => $this->faker->bothify('??###'),
            'quantity' => $quantity,
            'average_buy_price_paise' => $avgBuy,
            'total_invested_paise' => $invested,
            'current_value_paise' => $current,
            'unrealised_pnl_paise' => $current - $invested,
        ];
    }
}
