<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Season end reward tier per league.
 */
final class SeasonReward extends Model
{
    protected $fillable = [
        'season_id', 'league_id', 'rank_from', 'rank_to',
        'coin_reward', 'xp_reward', 'title_reward', 'extra_rewards',
    ];

    protected function casts(): array
    {
        return [
            'rank_from' => 'integer',
            'rank_to' => 'integer',
            'coin_reward' => 'integer',
            'xp_reward' => 'integer',
            'extra_rewards' => 'array',
        ];
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
