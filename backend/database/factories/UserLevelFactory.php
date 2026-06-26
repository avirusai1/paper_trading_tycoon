<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

final class UserLevelFactory extends Factory
{
    protected $model = UserLevel::class;

    private const TITLE_MAP = [
        1 => 'Student Trader',
        6 => 'Intern Trader',
        11 => 'Junior Trader',
        16 => 'Retail Trader',
        21 => 'Professional Trader',
    ];

    public function definition(): array
    {
        $level = $this->faker->numberBetween(1, 25);
        $xp = $level * 500 + $this->faker->numberBetween(0, 499);

        $title = 'Student Trader';
        foreach (self::TITLE_MAP as $minLevel => $t) {
            if ($level >= $minLevel) {
                $title = $t;
            }
        }

        return [
            'user_id' => User::factory(),
            'current_level' => $level,
            'current_xp' => $xp,
            'xp_in_current_level' => $xp % 500,
            'career_title' => $title,
            'level_achieved_at' => $this->faker->dateTimeBetween('-180 days', 'now'),
        ];
    }
}
