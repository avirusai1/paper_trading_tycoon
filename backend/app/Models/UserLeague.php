<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-user league membership per season.
 */
final class UserLeague extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'league_id', 'season_id', 'tier', 'rank_position',
        'season_portfolio_value_paise', 'season_return_percent', 'season_result', 'rewards_claimed',
    ];

    protected function casts(): array
    {
        return [
            'rank_position' => 'integer',
            'season_portfolio_value_paise' => 'integer',
            'season_return_percent' => 'float',
            'rewards_claimed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
