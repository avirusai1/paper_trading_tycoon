<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * End-of-day OHLCV data per stock — used for charts and historical P&L.
 */
final class StockDailyHistory extends Model
{
    protected $fillable = [
        'stock_id', 'symbol', 'trading_date',
        'open_paise', 'high_paise', 'low_paise', 'close_paise', 'volume',
    ];

    protected function casts(): array
    {
        return [
            'trading_date' => 'date',
            'open_paise' => 'integer',
            'high_paise' => 'integer',
            'low_paise' => 'integer',
            'close_paise' => 'integer',
            'volume' => 'integer',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
