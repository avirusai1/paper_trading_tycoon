<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Season competition period definition.
 */
final class Season extends Model
{
    protected $fillable = [
        'name', 'season_number', 'starts_at', 'ends_at', 'status', 'description', 'special_rules',
    ];

    protected function casts(): array
    {
        return [
            'season_number' => 'integer',
            'starts_at'     => 'date',
            'ends_at'       => 'date',
            'special_rules' => 'array',
        ];
    }

    public function userLeagues(): HasMany
    {
        return $this->hasMany(UserLeague::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(SeasonReward::class);
    }

    public function leaderboards(): HasMany
    {
        return $this->hasMany(Leaderboard::class);
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'active');
    }
}
