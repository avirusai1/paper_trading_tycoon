<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RewardHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class RewardHistoryFactory extends Factory
{
    protected $model = RewardHistory::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'source_type' => $this->faker->randomElement(['xp', 'coins']),
            'source_id' => $this->faker->uuid(),
            'xp_amount' => $this->faker->numberBetween(0, 1000),
            'coin_amount' => $this->faker->numberBetween(0, 5000),
            'description' => $this->faker->sentence(),
        ];
    }
}
