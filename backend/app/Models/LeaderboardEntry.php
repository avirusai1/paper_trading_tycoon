<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Computed leaderboard ranking entry — refreshed by scheduled job.
 */
final class LeaderboardEntry extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'leaderboard_id', 'user_id', 'rank_position',
        'score_value', 'score_display', 'score_label', 'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'rank_position' => 'integer',
            'score_value'   => 'integer',
            'score_display' => 'float',
            'computed_at'   => 'datetime',
        ];
    }

    public function leaderboard(): BelongsTo
    {
        return $this->belongsTo(Leaderboard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
