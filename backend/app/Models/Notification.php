<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Notification template/broadcast definition.
 */
final class Notification extends Model
{
    protected $fillable = ['key', 'title', 'body', 'type', 'data', 'is_broadcast'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_broadcast' => 'boolean',
        ];
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }
}
