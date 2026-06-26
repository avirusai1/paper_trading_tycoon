<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LeagueTier;
use App\Models\League;
use App\Models\Season;
use App\Models\User;
use App\Models\UserLeague;
use Illuminate\Database\Eloquent\Factories\Factory;

final class UserLeagueFactory extends Factory
{
    protected $model = UserLeague::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'league_id' => League::factory(),
            'season_id' => Season::factory(),
            'tier' => fn (array $attributes) => League::find($attributes['league_id'])?->tier?->value ?? LeagueTier::Bronze->value,
            'rank_position' => $this->faker->numberBetween(1, 100),
            'season_portfolio_value_paise' => 100_000_000,
            'season_return_percent' => 0.00,
            'season_result' => 'pending',
            'rewards_claimed' => false,
        ];
    }
}
