<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CoinTransactionSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only coin ledger entry (ADR-004).
 * Never updated or deleted. Balance derived from SUM(amount).
 */
final class CoinTransaction extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'amount', 'source_type', 'source_id',
        'balance_after', 'description', 'granted_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_after' => 'integer',
            'source_type' => CoinTransactionSource::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function isCredit(): bool
    {
        return $this->amount > 0;
    }

    public function isDebit(): bool
    {
        return $this->amount < 0;
    }

    public function scopeCredits(Builder $query): Builder
    {
        return $query->where('amount', '>', 0);
    }

    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('amount', '<', 0);
    }
}
