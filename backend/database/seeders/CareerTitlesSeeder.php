<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CareerTitle;
use Illuminate\Database\Seeder;

/**
 * Seeds career title definitions from gamification config.
 */
final class CareerTitlesSeeder extends Seeder
{
    public function run(): void
    {
        $titles = [
            ['title' => 'Student Trader',      'min_level' => 1,  'max_level' => 5,   'color_hex' => '#9E9E9E', 'sort_order' => 1],
            ['title' => 'Intern Trader',        'min_level' => 6,  'max_level' => 10,  'color_hex' => '#4CAF50', 'sort_order' => 2],
            ['title' => 'Junior Trader',        'min_level' => 11, 'max_level' => 15,  'color_hex' => '#2196F3', 'sort_order' => 3],
            ['title' => 'Retail Trader',        'min_level' => 16, 'max_level' => 20,  'color_hex' => '#9C27B0', 'sort_order' => 4],
            ['title' => 'Professional Trader',  'min_level' => 21, 'max_level' => 30,  'color_hex' => '#FF9800', 'sort_order' => 5],
            ['title' => 'Senior Trader',        'min_level' => 31, 'max_level' => 40,  'color_hex' => '#FF5722', 'sort_order' => 6],
            ['title' => 'Fund Manager',         'min_level' => 41, 'max_level' => 50,  'color_hex' => '#F44336', 'sort_order' => 7],
            ['title' => 'Portfolio Manager',    'min_level' => 51, 'max_level' => 60,  'color_hex' => '#E91E63', 'sort_order' => 8],
            ['title' => 'Hedge Fund Manager',   'min_level' => 61, 'max_level' => 75,  'color_hex' => '#673AB7', 'sort_order' => 9],
            ['title' => 'Market Legend',        'min_level' => 76, 'max_level' => 9999, 'color_hex' => '#FFC107', 'sort_order' => 10],
        ];

        foreach ($titles as $title) {
            CareerTitle::updateOrCreate(['title' => $title['title']], $title);
        }
    }
}
