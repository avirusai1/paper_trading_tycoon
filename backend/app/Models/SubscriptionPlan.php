<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Premium subscription plan catalogue.
 */
final class SubscriptionPlan extends Model
{
    protected $fillable = [
        'key', 'name', 'description', 'price_paise', 'duration_days', 'features', 'is_active', 'trial_days',
    ];

    protected function casts(): array
    {
        return [
            'price_paise' => 'integer',
            'duration_days' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'trial_days' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
