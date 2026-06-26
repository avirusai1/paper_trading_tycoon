<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

/**
 * Seeds all feature flags defaulting to disabled.
 * Enable via .env FLAG_* vars or directly in the DB.
 */
final class FeatureFlagsSeeder extends Seeder
{
    public function run(): void
    {
        $flags = [
            ['key' => 'crypto_trading',    'name' => 'Crypto Trading',         'group' => 'trading',       'description' => 'Allow trading of crypto assets'],
            ['key' => 'options_trading',   'name' => 'Options Trading',         'group' => 'trading',       'description' => 'F&O options paper trading'],
            ['key' => 'battle_pass',       'name' => 'Battle Pass',             'group' => 'monetization',  'description' => 'Season battle pass system'],
            ['key' => 'ai_coach',          'name' => 'AI Trading Coach',        'group' => 'ai',            'description' => 'AI-powered trading suggestions'],
            ['key' => 'copy_trading',      'name' => 'Copy Trading',            'group' => 'social',        'description' => 'Copy trades from top players'],
            ['key' => 'tournaments',       'name' => 'Tournaments',             'group' => 'game',          'description' => 'Short-duration trading tournaments'],
            ['key' => 'advanced_analytics', 'name' => 'Advanced Analytics',      'group' => 'analytics',     'description' => 'Advanced portfolio analytics charts'],
            ['key' => 'social_feed',       'name' => 'Social Feed',             'group' => 'social',        'description' => 'Activity feed and social interactions'],
            ['key' => 'market_news',       'name' => 'Market News',             'group' => 'market',        'description' => 'Curated market news in-app'],
            ['key' => 'ipo_tracking',      'name' => 'IPO Tracking',            'group' => 'market',        'description' => 'Upcoming IPO information'],
            ['key' => 'mutual_funds',      'name' => 'Mutual Funds',            'group' => 'trading',       'description' => 'Mutual fund paper trading simulation'],
            ['key' => 'dark_pool',         'name' => 'Dark Pool Simulator',     'group' => 'trading',       'description' => 'Simulated dark pool order routing'],
        ];

        foreach ($flags as $flag) {
            FeatureFlag::updateOrCreate(
                ['key' => $flag['key']],
                array_merge($flag, ['is_enabled' => false, 'rollout_percentage' => 0])
            );
        }
    }
}
