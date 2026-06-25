<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderSide;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable executed trade record — source of truth for P&L.
 */
final class Trade extends Model
{
    use HasFactory;

    public const UPDATED_AT = null; // Immutable

    protected $fillable = [
        'user_id', 'order_id', 'stock_id', 'symbol', 'side',
        'quantity', 'price_paise', 'total_value_paise',
        'brokerage_paise', 'net_value_paise', 'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'side'               => OrderSide::class,
            'quantity'           => 'integer',
            'price_paise'        => 'integer',
            'total_value_paise'  => 'integer',
            'brokerage_paise'    => 'integer',
            'net_value_paise'    => 'integer',
            'executed_at'        => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function scopeBuys(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('side', OrderSide::Buy);
    }

    public function scopeSells(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('side', OrderSide::Sell);
    }
}
