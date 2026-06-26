<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

final class SeasonFactory extends Factory
{
    protected $model = Season::class;

    public function definition(): array
    {
        $starts = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $ends = (clone $starts)->modify('+30 days');

        return [
            'name' => 'Season '.$this->faker->word(),
            'season_number' => $this->faker->unique()->numberBetween(1, 100),
            'starts_at' => $starts,
            'ends_at' => $ends,
            'status' => 'upcoming',
            'description' => $this->faker->sentence(),
            'special_rules' => null,
        ];
    }
}
