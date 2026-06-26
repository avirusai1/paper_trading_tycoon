<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

final class LevelFactory extends Factory
{
    protected $model = Level::class;

    public function definition(): array
    {
        $levelNum = $this->faker->unique()->numberBetween(1, 100);

        return [
            'level_number' => $levelNum,
            'xp_required' => $levelNum * 1000,
            'xp_to_next_level' => 1000,
            'coin_reward' => 100,
            'career_title' => 'Novice',
            'unlocks' => null,
            'badge_icon' => null,
        ];
    }
}
