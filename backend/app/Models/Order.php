<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderSide;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trade order — supports market, limit, stop, bracket, and partial fills.
 */
final class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'stock_id', 'symbol', 'idempotency_key', 'side', 'order_type',
        'status', 'quantity', 'filled_quantity', 'limit_price_paise',
        'stop_price_paise', 'average_fill_price_paise', 'rejection_reason', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'side' => OrderSide::class,
            'quantity' => 'integer',
            'filled_quantity' => 'integer',
            'limit_price_paise' => 'integer',
            'stop_price_paise' => 'integer',
            'average_fill_price_paise' => 'integer',
            'expires_at' => 'datetime',
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

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->orderBy('occurred_at');
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'open', 'partially_filled']);
    }

    public function isFilled(): bool
    {
        return $this->status === 'filled';
    }

    public function remainingQuantity(): int
    {
        return $this->quantity - $this->filled_quantity;
    }
}
