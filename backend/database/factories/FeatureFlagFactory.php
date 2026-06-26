<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeatureFlag;
use Illuminate\Database\Eloquent\Factories\Factory;

final class FeatureFlagFactory extends Factory
{
    protected $model = FeatureFlag::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->word(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'is_enabled' => $this->faker->boolean(),
            'rollout_percentage' => 0,
            'premium_only' => false,
            'allowed_user_ids' => null,
            'group' => null,
        ];
    }
}
