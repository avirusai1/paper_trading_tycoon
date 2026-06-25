<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\PremiumPlan;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Core authentication model.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $email_verified_at
 * @property string $referral_code
 * @property string|null $referred_by
 * @property string $status
 * @property bool $is_premium
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'referral_code',
        'referred_by',
        'status',
        'is_premium',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_premium'        => 'boolean',
            'password'          => 'hashed',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function level(): HasOne
    {
        return $this->hasOne(UserLevel::class);
    }

    public function holdings(): HasMany
    {
        return $this->hasMany(Holding::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function portfolioSnapshots(): HasMany
    {
        return $this->hasMany(PortfolioSnapshot::class);
    }

    public function xpLogs(): HasMany
    {
        return $this->hasMany(XpLog::class);
    }

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }

    public function rewardHistory(): HasMany
    {
        return $this->hasMany(RewardHistory::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function missions(): HasMany
    {
        return $this->hasMany(UserMission::class);
    }

    public function leagues(): HasMany
    {
        return $this->hasMany(UserLeague::class);
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(UserInventory::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function referralsGiven(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referralReceived(): HasOne
    {
        return $this->hasOne(Referral::class, 'referee_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePremium(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_premium', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }
}
