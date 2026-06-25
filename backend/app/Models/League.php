<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\LeagueTier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * League tier definition.
 */
final class League extends Model
{
    protected $fillable = [
        'tier', 'name', 'rank', 'promote_top_percent', 'demote_bottom_percent',
        'season_coin_reward', 'season_xp_reward', 'badge_icon', 'color_hex',
    ];

    protected function casts(): array
    {
        return [
            'tier'                  => LeagueTier::class,
            'rank'                  => 'integer',
            'promote_top_percent'   => 'float',
            'demote_bottom_percent' => 'float',
            'season_coin_reward'    => 'integer',
            'season_xp_reward'      => 'integer',
        ];
    }

    public function userLeagues(): HasMany
    {
        return $this->hasMany(UserLeague::class);
    }

    public function seasonRewards(): HasMany
    {
        return $this->hasMany(SeasonReward::class);
    }
}
