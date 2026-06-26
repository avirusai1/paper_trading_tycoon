<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Mission/challenge template catalogue.
 */
final class Mission extends Model
{
    protected $fillable = [
        'key', 'name', 'description', 'type', 'difficulty', 'category',
        'criteria', 'xp_reward', 'coin_reward', 'target_count',
        'is_active', 'available_from', 'available_until',
    ];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
            'xp_reward' => 'integer',
            'coin_reward' => 'integer',
            'target_count' => 'integer',
            'is_active' => 'boolean',
            'available_from' => 'datetime',
            'available_until' => 'datetime',
        ];
    }

    public function userMissions(): HasMany
    {
        return $this->hasMany(UserMission::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDaily(Builder $query): Builder
    {
        return $query->where('type', 'daily');
    }

    public function scopeWeekly(Builder $query): Builder
    {
        return $query->where('type', 'weekly');
    }
}
