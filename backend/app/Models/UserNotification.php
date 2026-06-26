<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-user notification delivery and read state.
 */
final class UserNotification extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'notification_id', 'status', 'is_read',
        'push_sent', 'sent_at', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'push_sent' => 'boolean',
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
