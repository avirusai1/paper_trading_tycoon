<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\StoreCategory;
use Illuminate\Database\Seeder;

final class StoreCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['key' => 'avatar_frames',    'name' => 'Avatar Frames',    'description' => 'Decorate your profile picture',  'sort_order' => 1],
            ['key' => 'profile_badges',   'name' => 'Profile Badges',   'description' => 'Show off unique badges',          'sort_order' => 2],
            ['key' => 'xp_boosts',        'name' => 'XP Boosts',        'description' => 'Accelerate your XP gains',        'sort_order' => 3],
            ['key' => 'portfolio_themes', 'name' => 'Portfolio Themes', 'description' => 'Customize your portfolio view',   'sort_order' => 4],
            ['key' => 'hints',            'name' => 'Hints & Helpers',  'description' => 'Get in-game trading hints',       'sort_order' => 5],
        ];

        foreach ($categories as $cat) {
            StoreCategory::updateOrCreate(['key' => $cat['key']], array_merge($cat, ['is_active' => true]));
        }
    }
}
