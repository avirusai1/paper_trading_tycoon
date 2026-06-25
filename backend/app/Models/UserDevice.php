<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registered device for push notifications and fraud detection.
 */
final class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'device_id', 'platform', 'fcm_token',
        'app_version', 'os_version', 'device_model', 'is_active', 'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }
}
