<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MarketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Current (latest) market quote per stock.
 */
final class StockPrice extends Model
{
    protected $fillable = [
        'stock_id', 'symbol', 'ltp_paise', 'open_paise', 'high_paise',
        'low_paise', 'close_paise', 'change_paise', 'change_percent',
        'volume', 'market_status', 'quoted_at',
    ];

    protected function casts(): array
    {
        return [
            'ltp_paise' => 'integer',
            'open_paise' => 'integer',
            'high_paise' => 'integer',
            'low_paise' => 'integer',
            'close_paise' => 'integer',
            'change_paise' => 'integer',
            'change_percent' => 'float',
            'volume' => 'integer',
            'market_status' => MarketStatus::class,
            'quoted_at' => 'datetime',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function isGain(): bool
    {
        return $this->change_paise >= 0;
    }
}
