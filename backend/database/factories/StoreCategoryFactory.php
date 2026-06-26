<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StoreCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

final class StoreCategoryFactory extends Factory
{
    protected $model = StoreCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
