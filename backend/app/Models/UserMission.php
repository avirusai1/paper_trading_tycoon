<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-user mission progress within a cycle.
 */
final class UserMission extends Model
{
    protected $fillable = [
        'user_id', 'mission_id', 'status', 'progress', 'target',
        'assigned_at', 'completed_at', 'claimed_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'progress'     => 'integer',
            'target'       => 'integer',
            'assigned_at'  => 'datetime',
            'completed_at' => 'datetime',
            'claimed_at'   => 'datetime',
            'expires_at'   => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function isComplete(): bool
    {
        return $this->progress >= $this->target;
    }

    public function progressPercent(): float
    {
        if ($this->target === 0) {
            return 100.0;
        }
        return min(100.0, ($this->progress / $this->target) * 100);
    }
}
