<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

/**
 * Seeds level 1–100 with progressive XP requirements.
 * Formula: xp_required = round(100 * (level ^ 1.5)) for a smooth curve.
 */
final class LevelsSeeder extends Seeder
{
    public function run(): void
    {
        $careerTitleMap = [
            1 => 'Student Trader',
            6 => 'Intern Trader',
            11 => 'Junior Trader',
            16 => 'Retail Trader',
            21 => 'Professional Trader',
            31 => 'Senior Trader',
            41 => 'Fund Manager',
            51 => 'Portfolio Manager',
            61 => 'Hedge Fund Manager',
            76 => 'Market Legend',
        ];

        $cumulative = 0;

        for ($level = 1; $level <= 100; $level++) {
            $xpToNext = (int) round(100 * ($level ** 1.5));
            $cumulative += ($level === 1 ? 0 : (int) round(100 * (($level - 1) ** 1.5)));

            // Determine career title at this level
            $title = 'Student Trader';
            foreach ($careerTitleMap as $minLevel => $t) {
                if ($level >= $minLevel) {
                    $title = $t;
                }
            }

            // Coin reward scales with level milestones
            $coinReward = match (true) {
                $level % 10 === 0 => 1000,
                $level % 5 === 0 => 500,
                default => 200,
            };

            Level::updateOrCreate(
                ['level_number' => $level],
                [
                    'xp_required' => $cumulative,
                    'xp_to_next_level' => $xpToNext,
                    'coin_reward' => $coinReward,
                    'career_title' => $title,
                    'unlocks' => null,
                ]
            );
        }
    }
}
