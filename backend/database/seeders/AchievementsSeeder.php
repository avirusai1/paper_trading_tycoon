<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

final class AchievementsSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            // Trading milestones
            ['key' => 'first_trade',          'name' => 'First Blood',           'description' => 'Execute your first trade.',                           'tier' => 'bronze',   'xp_reward' => 75,  'coin_reward' => 100,  'category' => 'trading',   'criteria' => ['type' => 'trade_count', 'target' => 1]],
            ['key' => 'trades_10',            'name' => 'Getting Started',        'description' => 'Execute 10 trades.',                                  'tier' => 'bronze',   'xp_reward' => 75,  'coin_reward' => 100,  'category' => 'trading',   'criteria' => ['type' => 'trade_count', 'target' => 10]],
            ['key' => 'trades_100',           'name' => 'Active Trader',          'description' => 'Execute 100 trades.',                                 'tier' => 'silver',   'xp_reward' => 150, 'coin_reward' => 250,  'category' => 'trading',   'criteria' => ['type' => 'trade_count', 'target' => 100]],
            ['key' => 'trades_1000',          'name' => 'Trading Machine',        'description' => 'Execute 1,000 trades.',                               'tier' => 'gold',     'xp_reward' => 300, 'coin_reward' => 500,  'category' => 'trading',   'criteria' => ['type' => 'trade_count', 'target' => 1000]],
            // Portfolio milestones
            ['key' => 'portfolio_5pct',       'name' => 'Positive Territory',     'description' => 'Achieve +5% portfolio return.',                       'tier' => 'bronze',   'xp_reward' => 75,  'coin_reward' => 100,  'category' => 'portfolio', 'criteria' => ['type' => 'portfolio_return_percent', 'target' => 5]],
            ['key' => 'portfolio_20pct',      'name' => 'Strong Performer',       'description' => 'Achieve +20% portfolio return.',                      'tier' => 'silver',   'xp_reward' => 150, 'coin_reward' => 250,  'category' => 'portfolio', 'criteria' => ['type' => 'portfolio_return_percent', 'target' => 20]],
            ['key' => 'portfolio_50pct',      'name' => 'Wealth Creator',         'description' => 'Achieve +50% portfolio return.',                      'tier' => 'gold',     'xp_reward' => 300, 'coin_reward' => 500,  'category' => 'portfolio', 'criteria' => ['type' => 'portfolio_return_percent', 'target' => 50]],
            ['key' => 'portfolio_100pct',     'name' => 'Doubler',                'description' => 'Double your starting portfolio value.',               'tier' => 'platinum', 'xp_reward' => 500, 'coin_reward' => 1000, 'category' => 'portfolio', 'criteria' => ['type' => 'portfolio_return_percent', 'target' => 100]],
            // Level milestones
            ['key' => 'level_10',             'name' => 'Level 10 Unlocked',      'description' => 'Reach level 10.',                                     'tier' => 'bronze',   'xp_reward' => 75,  'coin_reward' => 100,  'category' => 'game',      'criteria' => ['type' => 'level', 'target' => 10]],
            ['key' => 'level_25',             'name' => 'Rising Star',            'description' => 'Reach level 25.',                                     'tier' => 'silver',   'xp_reward' => 150, 'coin_reward' => 250,  'category' => 'game',      'criteria' => ['type' => 'level', 'target' => 25]],
            ['key' => 'level_50',             'name' => 'Veteran Trader',         'description' => 'Reach level 50.',                                     'tier' => 'gold',     'xp_reward' => 300, 'coin_reward' => 500,  'category' => 'game',      'criteria' => ['type' => 'level', 'target' => 50]],
            ['key' => 'level_76',             'name' => 'Market Legend Status',   'description' => 'Reach level 76 and earn the Market Legend title.',    'tier' => 'platinum', 'xp_reward' => 500, 'coin_reward' => 1000, 'category' => 'game',      'criteria' => ['type' => 'level', 'target' => 76]],
            // Social
            ['key' => 'first_referral',       'name' => 'Recruiter',              'description' => 'Refer your first friend.',                             'tier' => 'bronze',   'xp_reward' => 75,  'coin_reward' => 100,  'category' => 'social',    'criteria' => ['type' => 'referral_count', 'target' => 1]],
            ['key' => 'referrals_10',         'name' => 'Network Builder',        'description' => 'Refer 10 friends.',                                   'tier' => 'silver',   'xp_reward' => 150, 'coin_reward' => 250,  'category' => 'social',    'criteria' => ['type' => 'referral_count', 'target' => 10]],
            // Hidden
            ['key' => 'login_streak_30',      'name' => '???',                    'description' => 'Login 30 days in a row.',                             'tier' => 'hidden',   'xp_reward' => 300, 'coin_reward' => 500,  'category' => 'game',      'criteria' => ['type' => 'login_streak', 'target' => 30]],
            ['key' => 'profit_on_100_stocks', 'name' => '???',                    'description' => 'Make profit on trades in 100 different stocks.',      'tier' => 'hidden',   'xp_reward' => 500, 'coin_reward' => 1000, 'category' => 'trading',   'criteria' => ['type' => 'unique_profitable_stocks', 'target' => 100]],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(['key' => $achievement['key']], array_merge($achievement, ['is_active' => true, 'is_repeatable' => false]));
        }
    }
}
