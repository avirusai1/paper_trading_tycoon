<?php

declare(strict_types=1);

namespace App\Portfolio\Contracts;

use App\Portfolio\DTOs\PortfolioAnalyticsResult;

/**
 * Interface AnalyticsRepositoryContract
 *
 * Defines contract for caching and loading portfolio analytics.
 */
interface AnalyticsRepositoryContract
{
    /**
     * Caches calculated portfolio analytics for a given user.
     *
     * @param int $userId
     * @param PortfolioAnalyticsResult $analytics
     * @return void
     */
    public function save(int $userId, PortfolioAnalyticsResult $analytics): void;

    /**
     * Retrieves cached portfolio analytics for a given user.
     *
     * @param int $userId
     * @return PortfolioAnalyticsResult|null
     */
    public function get(int $userId): ?PortfolioAnalyticsResult;
}
