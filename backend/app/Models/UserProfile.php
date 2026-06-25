<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Extended user profile — display name, avatar, bio, location, streaks.
 */
final class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'display_name', 'avatar_url', 'bio', 'date_of_birth',
        'city', 'state', 'country', 'timezone', 'preferred_language',
        'total_trades', 'total_portfolio_value_paise',
        'last_active_at', 'last_login_at', 'login_streak', 'last_login_date',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'                => 'date',
            'last_active_at'               => 'datetime',
            'last_login_at'                => 'datetime',
            'last_login_date'              => 'date',
            'login_streak'                 => 'integer',
            'total_trades'                 => 'integer',
            'total_portfolio_value_paise'  => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
