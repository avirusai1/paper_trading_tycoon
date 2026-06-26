<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

final class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    private const CITIES = [
        'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai',
        'Kolkata', 'Pune', 'Ahmedabad', 'Jaipur', 'Lucknow',
    ];

    private const STATES = [
        'Maharashtra', 'Delhi', 'Karnataka', 'Telangana', 'Tamil Nadu',
        'West Bengal', 'Gujarat', 'Rajasthan', 'Uttar Pradesh',
    ];

    public function definition(): array
    {
        return [
            'display_name' => $this->faker->name(),
            'avatar_url' => null,
            'bio' => $this->faker->optional()->sentence(8),
            'date_of_birth' => $this->faker->dateTimeBetween('-45 years', '-18 years')->format('Y-m-d'),
            'city' => $this->faker->randomElement(self::CITIES),
            'state' => $this->faker->randomElement(self::STATES),
            'country' => 'IN',
            'timezone' => 'Asia/Kolkata',
            'preferred_language' => 'en',
            'total_trades' => $this->faker->numberBetween(0, 200),
            'total_portfolio_value_paise' => $this->faker->numberBetween(5000000, 200000000),
            'last_active_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'last_login_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'login_streak' => $this->faker->numberBetween(0, 30),
            'last_login_date' => now()->toDateString(),
        ];
    }
}
