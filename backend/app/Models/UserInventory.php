<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Items owned by a user.
 */
final class UserInventory extends Model
{
    protected $fillable = [
        'user_id', 'store_item_id', 'quantity', 'is_equipped',
        'metadata', 'expires_at', 'purchased_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity'     => 'integer',
            'is_equipped'  => 'boolean',
            'metadata'     => 'array',
            'expires_at'   => 'datetime',
            'purchased_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function storeItem(): BelongsTo
    {
        return $this->belongsTo(StoreItem::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
