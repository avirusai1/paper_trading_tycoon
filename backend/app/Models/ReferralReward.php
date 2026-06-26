<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reward granted to referrer or referee.
 */
final class ReferralReward extends Model
{
    protected $fillable = [
        'referral_id', 'user_id', 'recipient_type',
        'coin_amount', 'xp_amount', 'status', 'granted_at',
    ];

    protected function casts(): array
    {
        return [
            'coin_amount' => 'integer',
            'xp_amount' => 'integer',
            'granted_at' => 'datetime',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
