<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Generates realistic Indian users for testing and seeding.
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /** Common Indian first names */
    private const FIRST_NAMES = [
        'Arjun', 'Priya', 'Rahul', 'Anjali', 'Vikram', 'Neha', 'Rohan', 'Pooja',
        'Amit', 'Divya', 'Karan', 'Riya', 'Aditya', 'Sneha', 'Siddharth', 'Kavya',
        'Vivek', 'Meera', 'Nikhil', 'Tanvi', 'Harsh', 'Isha', 'Gaurav', 'Nisha',
        'Rajesh', 'Sunita', 'Deepak', 'Rekha', 'Suresh', 'Geeta', 'Manoj', 'Seema',
    ];

    /** Common Indian last names */
    private const LAST_NAMES = [
        'Sharma', 'Patel', 'Singh', 'Kumar', 'Gupta', 'Verma', 'Mehta', 'Joshi',
        'Reddy', 'Nair', 'Iyer', 'Rao', 'Mishra', 'Pandey', 'Srivastava', 'Agarwal',
        'Chatterjee', 'Banerjee', 'Bose', 'Das', 'Pillai', 'Menon', 'Shah', 'Desai',
    ];

    public function definition(): array
    {
        $firstName = $this->faker->randomElement(self::FIRST_NAMES);
        $lastName = $this->faker->randomElement(self::LAST_NAMES);

        return [
            'name' => "{$firstName} {$lastName}",
            'email' => strtolower("{$firstName}.{$lastName}".$this->faker->randomNumber(3)).'@example.com',
            'phone' => '+91'.$this->faker->numerify('9#########'),
            'email_verified_at' => now(),
            'password' => bcrypt('Password@123'),
            'referral_code' => strtoupper(Str::random(8)),
            'referred_by' => null,
            'status' => 'active',
            'is_premium' => false,
        ];
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null, 'status' => 'pending_verification']);
    }

    public function premium(): static
    {
        return $this->state(['is_premium' => true, 'status' => 'active']);
    }

    public function suspended(): static
    {
        return $this->state(['status' => 'suspended']);
    }
}
