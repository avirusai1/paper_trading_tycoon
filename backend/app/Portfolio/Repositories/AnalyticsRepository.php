<?php

declare(strict_types=1);

namespace App\Portfolio\Repositories;

use App\Portfolio\Contracts\AnalyticsRepositoryContract;
use App\Portfolio\DTOs\PortfolioAnalyticsResult;
use Illuminate\Support\Facades\Cache;

/**
 * Class AnalyticsRepository
 *
 * Cache-based implementation of AnalyticsRepositoryContract.
 */
final class AnalyticsRepository implements AnalyticsRepositoryContract
{
    private const CACHE_KEY_PREFIX = 'portfolio_analytics_';
    private const CACHE_TTL_SECONDS = 3600; // 1 hour

    public function save(int $userId, PortfolioAnalyticsResult $analytics): void
    {
        $key = self::CACHE_KEY_PREFIX . $userId;
        Cache::put($key, $analytics, self::CACHE_TTL_SECONDS);
    }

    public function get(int $userId): ?PortfolioAnalyticsResult
    {
        $key = self::CACHE_KEY_PREFIX . $userId;
        /** @var PortfolioAnalyticsResult|null */
        return Cache::get($key);
    }
}
