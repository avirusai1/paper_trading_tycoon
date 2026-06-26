<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LeagueTier;
use App\Models\League;
use Illuminate\Database\Eloquent\Factories\Factory;

final class LeagueFactory extends Factory
{
    protected $model = League::class;

    public function definition(): array
    {
        $tier = $this->faker->unique()->randomElement(LeagueTier::cases());

        return [
            'tier' => $tier,
            'name' => 'League '.ucfirst($tier->value),
            'rank' => $tier->rank(),
            'promote_top_percent' => 25.00,
            'demote_bottom_percent' => 25.00,
            'season_coin_reward' => 1000,
            'season_xp_reward' => 500,
            'badge_icon' => null,
            'color_hex' => '#FF5733',
        ];
    }
}
