<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A stock entry within a watchlist.
 */
final class WatchlistItem extends Model
{
    protected $fillable = ['watchlist_id', 'stock_id', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function watchlist(): BelongsTo
    {
        return $this->belongsTo(Watchlist::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
