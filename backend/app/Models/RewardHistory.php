<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Unified reward audit log (XP + coins from any source).
 */
final class RewardHistory extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'source_type', 'source_id', 'xp_amount', 'coin_amount', 'description',
    ];

    protected function casts(): array
    {
        return [
            'xp_amount'   => 'integer',
            'coin_amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
