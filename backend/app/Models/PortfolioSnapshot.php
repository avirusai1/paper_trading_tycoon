<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Point-in-time portfolio value snapshot for charts and leaderboards.
 */
final class PortfolioSnapshot extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'virtual_cash_paise', 'holdings_value_paise',
        'total_portfolio_value_paise', 'total_pnl_paise', 'total_pnl_percent',
        'total_holdings_count', 'snapshot_date', 'snapshot_type', 'taken_at',
    ];

    protected function casts(): array
    {
        return [
            'virtual_cash_paise'          => 'integer',
            'holdings_value_paise'        => 'integer',
            'total_portfolio_value_paise' => 'integer',
            'total_pnl_paise'             => 'integer',
            'total_pnl_percent'           => 'float',
            'total_holdings_count'        => 'integer',
            'snapshot_date'               => 'date',
            'taken_at'                    => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
