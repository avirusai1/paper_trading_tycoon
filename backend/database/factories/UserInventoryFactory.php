<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StoreItem;
use App\Models\User;
use App\Models\UserInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

final class UserInventoryFactory extends Factory
{
    protected $model = UserInventory::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'store_item_id' => StoreItem::factory(),
            'quantity' => 1,
            'is_equipped' => false,
            'metadata' => null,
            'expires_at' => null,
            'purchased_at' => now(),
        ];
    }
}
