<?php
declare(strict_types=1);

namespace App\GameEngine\Support;

use App\GameEngine\Enums\XPSource;
use App\Models\XpLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Tracks per-user, per-source daily XP totals to enforce daily caps.
 *
 * Uses a write-through cache to avoid an aggregation query on every event.
 * Cache TTL is set to end-of-day IST (Asia/Kolkata) so caps reset at midnight.
 */
final class DailyCapTracker
{
    private const CACHE_PREFIX = 'xp_daily';
    private const TIMEZONE     = 'Asia/Kolkata';

    /**
     * Return how many XP the user has already received from the given source today.
     */
    public function getDailyTotal(int $userId, XPSource $source): int
    {
        $key = $this->cacheKey($userId, $source);

        return (int) Cache::remember($key, $this->secondsUntilMidnight(), function () use ($userId, $source): int {
            return (int) XpLog::where('user_id', $userId)
                ->where('source', $source->value)
                ->whereDate('created_at', Carbon::now(self::TIMEZONE)->toDateString())
                ->sum('amount');
        });
    }

    /**
     * Increment the cached daily total for the given user/source.
     * Called after a successful XP grant to keep the cache in sync.
     */
    public function increment(int $userId, XPSource $source, int $amount): void
    {
        $key = $this->cacheKey($userId, $source);
        $ttl = $this->secondsUntilMidnight();

        // If key exists, increment; otherwise set (handles cache miss after grant)
        if (Cache::has($key)) {
            Cache::increment($key, $amount);
        } else {
            Cache::put($key, $this->getDailyTotal($userId, $source) + $amount, $ttl);
        }
    }

    private function cacheKey(int $userId, XPSource $source): string
    {
        $date = Carbon::now(self::TIMEZONE)->toDateString();
        return sprintf('%s:%d:%s:%s', self::CACHE_PREFIX, $userId, $source->value, $date);
    }

    private function secondsUntilMidnight(): int
    {
        $now      = Carbon::now(self::TIMEZONE);
        $midnight = $now->copy()->endOfDay();
        return max(60, (int) $now->diffInSeconds($midnight));
    }
}
