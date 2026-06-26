<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AchievementTier;
use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

final class AchievementFactory extends Factory
{
    protected $model = Achievement::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(3),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(10),
            'tier' => $this->faker->randomElement(AchievementTier::cases()),
            'xp_reward' => $this->faker->randomElement([75, 150, 300, 500]),
            'coin_reward' => $this->faker->randomElement([100, 250, 500, 1000]),
            'category' => $this->faker->randomElement(['trading', 'portfolio', 'social', 'game']),
            'criteria' => ['type' => 'trade_count', 'target' => $this->faker->numberBetween(1, 100)],
            'is_active' => true,
            'is_repeatable' => false,
        ];
    }
}
