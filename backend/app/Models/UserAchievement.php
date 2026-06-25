<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-user achievement unlock record.
 */
final class UserAchievement extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'achievement_id', 'unlock_count',
        'first_unlocked_at', 'last_unlocked_at',
    ];

    protected function casts(): array
    {
        return [
            'unlock_count'       => 'integer',
            'first_unlocked_at'  => 'datetime',
            'last_unlocked_at'   => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }
}
