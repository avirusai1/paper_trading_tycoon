<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Leaderboard definition.
 */
final class Leaderboard extends Model
{
    protected $fillable = [
        'key', 'name', 'type', 'period', 'season_id', 'league_id',
        'is_active', 'period_starts_at', 'period_ends_at', 'max_entries', 'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_entries' => 'integer',
            'period_starts_at' => 'date',
            'period_ends_at' => 'date',
            'last_updated_at' => 'datetime',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LeaderboardEntry::class)->orderBy('rank_position');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }
}
