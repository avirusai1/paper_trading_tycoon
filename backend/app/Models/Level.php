<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Level threshold definition with rewards and unlocks.
 */
final class Level extends Model
{
    use HasFactory;
    protected $fillable = [
        'level_number', 'xp_required', 'xp_to_next_level',
        'coin_reward', 'career_title', 'unlocks', 'badge_icon',
    ];

    protected function casts(): array
    {
        return [
            'level_number' => 'integer',
            'xp_required' => 'integer',
            'xp_to_next_level' => 'integer',
            'coin_reward' => 'integer',
            'unlocks' => 'array',
        ];
    }
}
