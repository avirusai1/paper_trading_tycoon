<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Current XP and level state per user.
 */
final class UserLevel extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'current_level', 'current_xp',
        'xp_in_current_level', 'career_title', 'level_achieved_at',
    ];

    protected function casts(): array
    {
        return [
            'current_level' => 'integer',
            'current_xp' => 'integer',
            'xp_in_current_level' => 'integer',
            'level_achieved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
