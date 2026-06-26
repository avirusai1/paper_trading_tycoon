<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;

final class MissionFactory extends Factory
{
    protected $model = Mission::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(3),
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->sentence(10),
            'type' => $this->faker->randomElement(['daily', 'weekly']),
            'difficulty' => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'category' => $this->faker->randomElement(['trading', 'portfolio', 'exploration']),
            'criteria' => ['type' => 'buy_trades', 'target' => $this->faker->numberBetween(1, 10)],
            'xp_reward' => $this->faker->randomElement([50, 100, 200]),
            'coin_reward' => $this->faker->randomElement([50, 100, 250]),
            'target_count' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }

    public function daily(): static
    {
        return $this->state(['type' => 'daily']);
    }

    public function weekly(): static
    {
        return $this->state(['type' => 'weekly']);
    }
}
