<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GameRule;
use Illuminate\Database\Eloquent\Factories\Factory;

final class GameRuleFactory extends Factory
{
    protected $model = GameRule::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->word().'.'.$this->faker->word(),
            'group' => $this->faker->randomElement(['xp', 'coins', 'leagues', 'missions', 'seasons', 'market']),
            'value' => (string) $this->faker->numberBetween(1, 1000),
            'value_type' => 'integer',
            'description' => $this->faker->sentence(),
            'is_overridable' => true,
        ];
    }
}
