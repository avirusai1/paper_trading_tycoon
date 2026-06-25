<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CoinTransactionSource;
use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class CoinTransactionFactory extends Factory
{
    protected $model = CoinTransaction::class;

    public function definition(): array
    {
        $source = $this->faker->randomElement(CoinTransactionSource::cases());
        $amount = $this->faker->numberBetween(10, 500);

        return [
            'user_id'      => User::factory(),
            'amount'       => $amount,
            'source_type'  => $source->value,
            'source_id'    => Str::uuid()->toString(),
            'balance_after' => $this->faker->numberBetween($amount, 5000),
            'description'  => "Coins from {$source->value}",
        ];
    }

    public function debit(): static
    {
        return $this->state(fn (array $attrs) => ['amount' => -abs($attrs['amount'])]);
    }
}
