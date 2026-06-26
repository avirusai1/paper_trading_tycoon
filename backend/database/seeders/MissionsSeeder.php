<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Mission;
use Illuminate\Database\Seeder;

final class MissionsSeeder extends Seeder
{
    public function run(): void
    {
        $missions = [
            // Daily missions
            ['key' => 'daily_buy_1',         'name' => 'Make a Move',          'description' => 'Execute 1 buy order today.',               'type' => 'daily',  'difficulty' => 'easy',   'category' => 'trading',   'criteria' => ['type' => 'buy_trades',   'target' => 1],  'xp_reward' => 50,  'coin_reward' => 50,  'target_count' => 1],
            ['key' => 'daily_buy_3',         'name' => 'Active Buyer',         'description' => 'Execute 3 buy orders today.',              'type' => 'daily',  'difficulty' => 'medium', 'category' => 'trading',   'criteria' => ['type' => 'buy_trades',   'target' => 3],  'xp_reward' => 100, 'coin_reward' => 100, 'target_count' => 3],
            ['key' => 'daily_sell_1',        'name' => 'Take Profit',          'description' => 'Execute 1 sell order today.',              'type' => 'daily',  'difficulty' => 'easy',   'category' => 'trading',   'criteria' => ['type' => 'sell_trades',  'target' => 1],  'xp_reward' => 50,  'coin_reward' => 50,  'target_count' => 1],
            ['key' => 'daily_login',         'name' => 'Daily Check-in',       'description' => 'Log in to the app today.',                 'type' => 'daily',  'difficulty' => 'easy',   'category' => 'exploration', 'criteria' => ['type' => 'login',        'target' => 1],  'xp_reward' => 25,  'coin_reward' => 10,  'target_count' => 1],
            ['key' => 'daily_watchlist',     'name' => 'Market Watcher',       'description' => 'Add a stock to your watchlist.',           'type' => 'daily',  'difficulty' => 'easy',   'category' => 'exploration', 'criteria' => ['type' => 'watchlist_add', 'target' => 1],  'xp_reward' => 25,  'coin_reward' => 25,  'target_count' => 1],
            ['key' => 'daily_explore_5',     'name' => 'Explorer',             'description' => 'View 5 different stock detail pages.',     'type' => 'daily',  'difficulty' => 'easy',   'category' => 'exploration', 'criteria' => ['type' => 'stock_views',  'target' => 5],  'xp_reward' => 25,  'coin_reward' => 25,  'target_count' => 5],
            // Weekly missions
            ['key' => 'weekly_trades_10',    'name' => 'Weekly Trader',        'description' => 'Execute 10 trades this week.',             'type' => 'weekly', 'difficulty' => 'medium', 'category' => 'trading',   'criteria' => ['type' => 'total_trades', 'target' => 10], 'xp_reward' => 200, 'coin_reward' => 200, 'target_count' => 10],
            ['key' => 'weekly_trades_25',    'name' => 'Heavy Trader',         'description' => 'Execute 25 trades this week.',             'type' => 'weekly', 'difficulty' => 'hard',   'category' => 'trading',   'criteria' => ['type' => 'total_trades', 'target' => 25], 'xp_reward' => 400, 'coin_reward' => 400, 'target_count' => 25],
            ['key' => 'weekly_profit',       'name' => 'Profitable Week',      'description' => 'End the week with positive P&L.',          'type' => 'weekly', 'difficulty' => 'medium', 'category' => 'portfolio', 'criteria' => ['type' => 'weekly_pnl_positive', 'target' => 1], 'xp_reward' => 200, 'coin_reward' => 200, 'target_count' => 1],
            ['key' => 'weekly_sectors_3',    'name' => 'Diversifier',          'description' => 'Trade stocks from 3 different sectors.',   'type' => 'weekly', 'difficulty' => 'medium', 'category' => 'portfolio', 'criteria' => ['type' => 'unique_sectors', 'target' => 3], 'xp_reward' => 200, 'coin_reward' => 200, 'target_count' => 3],
            // Tutorial missions (one-time)
            ['key' => 'tutorial_first_buy',  'name' => 'Your First Buy',       'description' => 'Buy your first stock.',                    'type' => 'tutorial', 'difficulty' => 'easy',  'category' => 'trading',   'criteria' => ['type' => 'buy_trades', 'target' => 1],   'xp_reward' => 100, 'coin_reward' => 50,  'target_count' => 1],
            ['key' => 'tutorial_watchlist',  'name' => 'Set Up Watchlist',     'description' => 'Add your first stock to a watchlist.',     'type' => 'tutorial', 'difficulty' => 'easy',  'category' => 'exploration', 'criteria' => ['type' => 'watchlist_add', 'target' => 1],  'xp_reward' => 50,  'coin_reward' => 25,  'target_count' => 1],
            ['key' => 'tutorial_profile',    'name' => 'Complete Your Profile', 'description' => 'Fill in your display name and bio.',       'type' => 'tutorial', 'difficulty' => 'easy',  'category' => 'exploration', 'criteria' => ['type' => 'profile_complete', 'target' => 1], 'xp_reward' => 50,  'coin_reward' => 25,  'target_count' => 1],
        ];

        foreach ($missions as $mission) {
            Mission::updateOrCreate(['key' => $mission['key']], array_merge($mission, ['is_active' => true]));
        }
    }
}
