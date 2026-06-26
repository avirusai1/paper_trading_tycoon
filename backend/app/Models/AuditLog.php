<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * General-purpose model change audit trail.
 */
final class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'event', 'auditable_type', 'auditable_id',
        'old_values', 'new_values', 'ip_address', 'url',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'auditable_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
