<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Stock master catalogue. Prices live in StockPrice.
 */
final class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol', 'name', 'exchange', 'isin', 'sector', 'industry',
        'logo_url', 'description', 'market_cap_paise',
        'is_active', 'is_nifty50', 'is_sensex', 'is_tradeable',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'is_nifty50'   => 'boolean',
            'is_sensex'    => 'boolean',
            'is_tradeable' => 'boolean',
            'market_cap_paise' => 'integer',
        ];
    }

    public function currentPrice(): HasOne
    {
        return $this->hasOne(StockPrice::class);
    }

    public function dailyHistory(): HasMany
    {
        return $this->hasMany(StockDailyHistory::class)->orderByDesc('trading_date');
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

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeTradeable(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_tradeable', true)->where('is_active', true);
    }

    public function scopeNifty50(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_nifty50', true);
    }
}
