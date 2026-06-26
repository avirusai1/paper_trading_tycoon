<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

final class SubscriptionPlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'key' => 'monthly',
                'name' => 'Premium Monthly',
                'description' => 'Full access to all premium features for 30 days.',
                'price_paise' => 9900,   // ₹99
                'duration_days' => 30,
                'trial_days' => 7,
                'features' => ['advanced_analytics', 'ai_coach', 'battle_pass', 'priority_support'],
                'is_active' => true,
            ],
            [
                'key' => 'annual',
                'name' => 'Premium Annual',
                'description' => 'Best value — full premium access for 365 days.',
                'price_paise' => 79900,  // ₹799 (saves ₹389 vs monthly)
                'duration_days' => 365,
                'trial_days' => 14,
                'features' => ['advanced_analytics', 'ai_coach', 'battle_pass', 'priority_support', 'exclusive_badge'],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['key' => $plan['key']], $plan);
        }
    }
}
