<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only admin action audit log.
 */
final class AdminLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'admin_user_id', 'action', 'target_type', 'target_id',
        'before', 'after', 'ip_address', 'user_agent', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
            'target_id' => 'integer',
        ];
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
