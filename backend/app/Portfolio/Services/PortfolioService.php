<?php

declare(strict_types=1);

namespace App\Portfolio\Services;

use App\Portfolio\Contracts\PortfolioServiceContract;
use App\Portfolio\Actions\RefreshPortfolioAction;
use App\Portfolio\Actions\CalculatePerformanceAction;
use App\Portfolio\DTOs\PortfolioResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Class PortfolioService
 *
 * Facade service for interacting with the Portfolio Engine subsystem.
 */
final readonly class PortfolioService implements PortfolioServiceContract
{
    private const CACHE_KEY_PREFIX = 'portfolio_result_';
    private const CACHE_TTL_SECONDS = 300; // 5 minutes cache

    public function __construct(
        private RefreshPortfolioAction $refreshAction,
        private CalculatePerformanceAction $calculatePerformanceAction
    ) {}

    public function refresh(int $userId): PortfolioResult
    {
        // Force recalculation by running the refresh action
        $result = $this->refreshAction->execute($userId, 'manual');

        // Store result in cache
        $cacheKey = self::CACHE_KEY_PREFIX . $userId;
        Cache::put($cacheKey, $result, self::CACHE_TTL_SECONDS);

        return $result;
    }

    public function refreshBatch(array $userIds): array
    {
        $results = [];
        foreach ($userIds as $userId) {
            $results[$userId] = $this->refresh($userId);
        }
        return $results;
    }

    public function getPortfolio(int $userId): PortfolioResult
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $userId;

        // Attempt read from cache. If missed, trigger refresh to recalculate
        /** @var PortfolioResult|null $cached */
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        return $this->refresh($userId);
    }

    public function reconstructHistory(
        int $userId,
        string $interval = 'daily',
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $perfResult = $this->calculatePerformanceAction->execute($userId, $interval, $startDate, $endDate);
        return $perfResult->performancePoints;
    }
}
