<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\StoreCategory;
use App\Models\StoreItem;
use Illuminate\Database\Seeder;

final class StoreItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // XP Boosts
            ['category' => 'xp_boosts', 'key' => 'xp_boost_2x_1h',   'name' => '2× XP Boost (1 hour)',   'coin_price' => 200, 'item_type' => 'xp_boost',  'required_level' => 5,  'effects' => ['xp_multiplier' => 2, 'duration_hours' => 1]],
            ['category' => 'xp_boosts', 'key' => 'xp_boost_2x_24h',  'name' => '2× XP Boost (24 hours)', 'coin_price' => 800, 'item_type' => 'xp_boost',  'required_level' => 10, 'effects' => ['xp_multiplier' => 2, 'duration_hours' => 24]],
            ['category' => 'xp_boosts', 'key' => 'xp_boost_3x_1h',   'name' => '3× XP Boost (1 hour)',   'coin_price' => 500, 'item_type' => 'xp_boost',  'required_level' => 15, 'effects' => ['xp_multiplier' => 3, 'duration_hours' => 1]],
            // Hints
            ['category' => 'hints',     'key' => 'sector_hint',       'name' => 'Sector Trend Hint',       'coin_price' => 100, 'item_type' => 'hint',      'required_level' => 1,  'effects' => ['hint_type' => 'sector_trend']],
            ['category' => 'hints',     'key' => 'stock_signal',      'name' => 'Stock Signal Hint',       'coin_price' => 150, 'item_type' => 'hint',      'required_level' => 5,  'effects' => ['hint_type' => 'stock_signal']],
            // Avatar Frames
            ['category' => 'avatar_frames', 'key' => 'frame_gold',    'name' => 'Gold Frame',              'coin_price' => 500, 'item_type' => 'avatar_frame', 'required_level' => 1, 'effects' => []],
            ['category' => 'avatar_frames', 'key' => 'frame_diamond', 'name' => 'Diamond Frame',           'coin_price' => 2000, 'item_type' => 'avatar_frame', 'required_level' => 30, 'effects' => [], 'is_premium_only' => true],
            // Profile Badges
            ['category' => 'profile_badges', 'key' => 'badge_bull',   'name' => 'Bull Badge',              'coin_price' => 300, 'item_type' => 'profile_badge', 'required_level' => 1,  'effects' => []],
            ['category' => 'profile_badges', 'key' => 'badge_bear',   'name' => 'Bear Badge',              'coin_price' => 300, 'item_type' => 'profile_badge', 'required_level' => 1,  'effects' => []],
            ['category' => 'profile_badges', 'key' => 'badge_legend', 'name' => 'Legend Badge',            'coin_price' => 5000, 'item_type' => 'profile_badge', 'required_level' => 76, 'effects' => []],
        ];

        foreach ($items as $item) {
            $category = StoreCategory::where('key', $item['category'])->first();
            if ($category === null) {
                continue;
            }
            StoreItem::updateOrCreate(['key' => $item['key']], [
                'store_category_id' => $category->id,
                'name' => $item['name'],
                'description' => $item['name'],
                'coin_price' => $item['coin_price'],
                'item_type' => $item['item_type'],
                'effects' => $item['effects'],
                'required_level' => $item['required_level'],
                'is_premium_only' => $item['is_premium_only'] ?? false,
                'is_active' => true,
            ]);
        }
    }
}
