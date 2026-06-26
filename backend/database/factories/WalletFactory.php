<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

final class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        $spent = $this->faker->numberBetween(0, 90_000_000);

        return [
            'user_id' => User::factory(),
            'virtual_cash_paise' => 100_000_000 - $spent,
            'coin_balance' => $this->faker->numberBetween(0, 5000),
            'total_deposited_paise' => 100_000_000,
            'total_withdrawn_paise' => $spent,
            'coin_balance_updated_at' => now(),
        ];
    }

    public function fresh(): static
    {
        return $this->state([
            'virtual_cash_paise' => 100_000_000,
            'coin_balance' => 0,
            'total_withdrawn_paise' => 0,
        ]);
    }
}
