<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User premium subscription.
 */
final class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'subscription_plan_id', 'status', 'amount_paid_paise',
        'payment_provider', 'payment_reference', 'starts_at', 'ends_at',
        'trial_ends_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid_paise' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->where('ends_at', '>', now());
    }
}
