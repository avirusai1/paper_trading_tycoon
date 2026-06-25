<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only order lifecycle event. Immutable once created.
 */
final class OrderEvent extends Model
{
    public const UPDATED_AT = null; // Append-only — no updated_at

    protected $fillable = [
        'order_id', 'user_id', 'event_type', 'from_status', 'to_status',
        'quantity', 'price_paise', 'metadata', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity'    => 'integer',
            'price_paise' => 'integer',
            'metadata'    => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
