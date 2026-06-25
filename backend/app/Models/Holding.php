<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Current stock position per user. Zero-quantity rows are retained for history.
 */
final class Holding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'stock_id', 'symbol', 'quantity',
        'average_buy_price_paise', 'total_invested_paise',
        'current_value_paise', 'unrealised_pnl_paise',
    ];

    protected function casts(): array
    {
        return [
            'quantity'                => 'integer',
            'average_buy_price_paise' => 'integer',
            'total_invested_paise'    => 'integer',
            'current_value_paise'     => 'integer',
            'unrealised_pnl_paise'    => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('quantity', '>', 0);
    }

    public function isInProfit(): bool
    {
        return $this->unrealised_pnl_paise > 0;
    }
}
