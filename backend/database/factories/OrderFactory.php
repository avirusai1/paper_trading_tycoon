<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderSide;
use App\Models\Order;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 100);
        $price = $this->faker->numberBetween(10000, 500000); // 100–5000 rupees in paise

        return [
            'user_id' => User::factory(),
            'stock_id' => Stock::factory(),
            'symbol' => $this->faker->bothify('??###'),
            'idempotency_key' => Str::uuid()->toString(),
            'side' => $this->faker->randomElement(OrderSide::cases()),
            'order_type' => 'market',
            'status' => 'filled',
            'quantity' => $quantity,
            'filled_quantity' => $quantity,
            'average_fill_price_paise' => $price,
        ];
    }

    public function buy(): static
    {
        return $this->state(['side' => OrderSide::Buy]);
    }

    public function sell(): static
    {
        return $this->state(['side' => OrderSide::Sell]);
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending', 'filled_quantity' => 0, 'average_fill_price_paise' => null]);
    }
}
