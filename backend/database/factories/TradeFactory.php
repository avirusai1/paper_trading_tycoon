<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderSide;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class TradeFactory extends Factory
{
    protected $model = Trade::class;

    public function definition(): array
    {
        $quantity   = $this->faker->numberBetween(1, 100);
        $pricePaise = $this->faker->numberBetween(10000, 500000);
        $total      = $quantity * $pricePaise;

        return [
            'user_id'           => User::factory(),
            'order_id'          => Order::factory(),
            'stock_id'          => Stock::factory(),
            'symbol'            => $this->faker->bothify('??###'),
            'side'              => $this->faker->randomElement(OrderSide::cases()),
            'quantity'          => $quantity,
            'price_paise'       => $pricePaise,
            'total_value_paise' => $total,
            'brokerage_paise'   => 0,
            'net_value_paise'   => $total,
            'executed_at'       => $this->faker->dateTimeBetween('-90 days', 'now'),
        ];
    }
}
