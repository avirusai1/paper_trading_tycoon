<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StoreCategory;
use App\Models\StoreItem;
use Illuminate\Database\Eloquent\Factories\Factory;

final class StoreItemFactory extends Factory
{
    protected $model = StoreItem::class;

    public function definition(): array
    {
        return [
            'store_category_id' => StoreCategory::factory(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price_coins' => 100,
            'sku' => $this->faker->unique()->slug(),
            'effects' => [],
            'is_active' => true,
        ];
    }
}
