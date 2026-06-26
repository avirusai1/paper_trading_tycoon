<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Referral relationship between referrer and referee.
 */
final class Referral extends Model
{
    protected $fillable = [
        'referrer_id', 'referee_id', 'referral_code', 'status',
        'flag_reason', 'registered_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class);
    }
}
