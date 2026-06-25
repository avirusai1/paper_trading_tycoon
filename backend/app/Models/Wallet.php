<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Virtual cash wallet and materialized coin balance per user.
 * Never mutated directly — only through trade execution transactions.
 */
final class Wallet extends Model
{
    protected $fillable = [
        'user_id', 'virtual_cash_paise', 'coin_balance',
        'total_deposited_paise', 'total_withdrawn_paise', 'coin_balance_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'virtual_cash_paise'     => 'integer',
            'coin_balance'           => 'integer',
            'total_deposited_paise'  => 'integer',
            'total_withdrawn_paise'  => 'integer',
            'coin_balance_updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class, 'user_id', 'user_id');
    }
}
