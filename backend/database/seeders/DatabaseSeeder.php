<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Master seeder — runs all seeders in dependency order.
 * Run with: php artisan db:seed
 */
final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Reference data first — no FK dependencies
            GameRulesSeeder::class,
            CareerTitlesSeeder::class,
            LevelsSeeder::class,
            LeaguesSeeder::class,
            FeatureFlagsSeeder::class,
            SystemSettingsSeeder::class,

            // Store (depends on categories)
            StoreCategoriesSeeder::class,
            StoreItemsSeeder::class,

            // Premium
            SubscriptionPlansSeeder::class,

            // Game content
            AchievementsSeeder::class,
            MissionsSeeder::class,

            // Market data (depends on stocks)
            StocksSeeder::class,
        ]);
    }
}
