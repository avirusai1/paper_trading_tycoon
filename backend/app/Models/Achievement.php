<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\AchievementTier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Achievement definition catalogue.
 */
final class Achievement extends Model
{
    protected $fillable = [
        'key', 'name', 'description', 'tier', 'icon_url',
        'xp_reward', 'coin_reward', 'category', 'criteria',
        'is_active', 'is_repeatable',
    ];

    protected function casts(): array
    {
        return [
            'tier'          => AchievementTier::class,
            'criteria'      => 'array',
            'xp_reward'     => 'integer',
            'coin_reward'   => 'integer',
            'is_active'     => 'boolean',
            'is_repeatable' => 'boolean',
        ];
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }
}
