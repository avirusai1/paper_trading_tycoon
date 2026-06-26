<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Runtime feature flag — read by FeatureFlagService with cache-aside.
 */
final class FeatureFlag extends Model
{
    use HasFactory;
    protected $fillable = [
        'key', 'name', 'description', 'is_enabled', 'rollout_percentage',
        'premium_only', 'allowed_user_ids', 'group',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'rollout_percentage' => 'integer',
            'premium_only' => 'boolean',
            'allowed_user_ids' => 'array',
        ];
    }

    public function isEnabledForUser(User $user): bool
    {
        if (! $this->is_enabled) {
            return false;
        }
        // Explicit allowlist check
        if (is_array($this->allowed_user_ids) && in_array($user->id, $this->allowed_user_ids, true)) {
            return true;
        }
        // Premium gate
        if ($this->premium_only && ! $user->is_premium) {
            return false;
        }
        // Percentage rollout
        if ($this->rollout_percentage < 100) {
            return (crc32("flag_{$user->id}") % 100) < $this->rollout_percentage;
        }

        return true;
    }
}
