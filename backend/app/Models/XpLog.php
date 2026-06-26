<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only XP transaction log.
 */
final class XpLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'amount', 'source', 'source_id',
        'xp_before', 'xp_after', 'level_before', 'level_after',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'xp_before' => 'integer',
            'xp_after' => 'integer',
            'level_before' => 'integer',
            'level_after' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function didLevelUp(): bool
    {
        return $this->level_after > $this->level_before;
    }
}
