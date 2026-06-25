<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Coin store item catalogue.
 */
final class StoreItem extends Model
{
    protected $fillable = [
        'store_category_id', 'key', 'name', 'description', 'coin_price',
        'item_type', 'effects', 'required_level', 'is_premium_only',
        'is_active', 'is_limited', 'stock_quantity', 'sold_count', 'image_url',
    ];

    protected function casts(): array
    {
        return [
            'coin_price'      => 'integer',
            'effects'         => 'array',
            'required_level'  => 'integer',
            'is_premium_only' => 'boolean',
            'is_active'       => 'boolean',
            'is_limited'      => 'boolean',
            'stock_quantity'  => 'integer',
            'sold_count'      => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(StoreCategory::class, 'store_category_id');
    }

    public function userInventory(): HasMany
    {
        return $this->hasMany(UserInventory::class);
    }

    public function isAvailable(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->is_limited && $this->stock_quantity !== null && $this->sold_count >= $this->stock_quantity) {
            return false;
        }
        return true;
    }
}
