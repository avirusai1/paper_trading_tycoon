<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Coin store category.
 */
final class StoreCategory extends Model
{
    protected $fillable = ['key', 'name', 'description', 'icon_url', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(StoreItem::class);
    }
}
