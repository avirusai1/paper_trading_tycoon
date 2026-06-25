<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\League;
use Illuminate\Database\Seeder;

/**
 * Seeds league tier definitions from LeagueTier enum.
 */
final class LeaguesSeeder extends Seeder
{
    public function run(): void
    {
        $leagues = [
            ['tier' => 'bronze',   'name' => 'Bronze League',   'rank' => 1, 'promote_top_percent' => 25.00, 'demote_bottom_percent' => 0.00,  'season_coin_reward' => 100,  'season_xp_reward' => 200,  'color_hex' => '#CD7F32'],
            ['tier' => 'silver',   'name' => 'Silver League',   'rank' => 2, 'promote_top_percent' => 25.00, 'demote_bottom_percent' => 25.00, 'season_coin_reward' => 250,  'season_xp_reward' => 500,  'color_hex' => '#C0C0C0'],
            ['tier' => 'gold',     'name' => 'Gold League',     'rank' => 3, 'promote_top_percent' => 25.00, 'demote_bottom_percent' => 25.00, 'season_coin_reward' => 500,  'season_xp_reward' => 1000, 'color_hex' => '#FFD700'],
            ['tier' => 'platinum', 'name' => 'Platinum League', 'rank' => 4, 'promote_top_percent' => 25.00, 'demote_bottom_percent' => 25.00, 'season_coin_reward' => 1000, 'season_xp_reward' => 2000, 'color_hex' => '#E5E4E2'],
            ['tier' => 'diamond',  'name' => 'Diamond League',  'rank' => 5, 'promote_top_percent' => 0.00,  'demote_bottom_percent' => 25.00, 'season_coin_reward' => 2500, 'season_xp_reward' => 5000, 'color_hex' => '#B9F2FF'],
        ];

        foreach ($leagues as $league) {
            League::updateOrCreate(['tier' => $league['tier']], $league);
        }
    }
}
