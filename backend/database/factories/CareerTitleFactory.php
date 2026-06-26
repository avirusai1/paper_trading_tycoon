<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CareerTitle;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CareerTitleFactory extends Factory
{
    protected $model = CareerTitle::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->jobTitle(),
            'min_level' => 1,
            'max_level' => 10,
            'description' => $this->faker->sentence(),
            'icon_url' => null,
            'color_hex' => '#000000',
            'sort_order' => 1,
        ];
    }
}
