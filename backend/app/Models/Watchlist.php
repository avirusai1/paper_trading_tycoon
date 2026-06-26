<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * User-defined watchlist.
 */
final class Watchlist extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'is_default', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WatchlistItem::class)->orderBy('sort_order');
    }

    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class, 'watchlist_items')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }
}
