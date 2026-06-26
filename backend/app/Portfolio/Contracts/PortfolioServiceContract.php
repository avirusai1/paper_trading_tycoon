<?php

declare(strict_types=1);

namespace App\Portfolio\Contracts;

use App\Portfolio\DTOs\PortfolioResult;
use Carbon\Carbon;

/**
 * Interface PortfolioServiceContract
 *
 * Facade contract for managing portfolio valuation, snapshots, risk, and analytics.
 */
interface PortfolioServiceContract
{
    /**
     * Refreshes portfolio state for a single user.
     * Calculates valuation, returns, analytics, risk metrics, generates a snapshot,
     * dispatches portfolio events, and returns the computed result.
     *
     * @param int $userId The unique user identifier.
     * @return PortfolioResult The computed portfolio snapshot and intelligence.
     */
    public function refresh(int $userId): PortfolioResult;

    /**
     * Refreshes portfolio state for a batch of users.
     *
     * @param array<int> $userIds Array of user identifiers.
     * @return array<int, PortfolioResult> Keyed by userId.
     */
    public function refreshBatch(array $userIds): array;

    /**
     * Gets the current portfolio state (retrieved from cache or computed on demand).
     *
     * @param int $userId The unique user identifier.
     * @return PortfolioResult The cached or calculated portfolio intelligence.
     */
    public function getPortfolio(int $userId): PortfolioResult;

    /**
     * Reconstructs historical portfolio snapshots for charting and performance analysis.
     *
     * @param int $userId The unique user identifier.
     * @param string $interval Interval for historical data (e.g., daily, weekly, monthly, yearly).
     * @param Carbon|null $startDate Filter start date.
     * @param Carbon|null $endDate Filter end date.
     * @return array<int, array<string, mixed>> Array of historical snapshot points.
     */
    public function reconstructHistory(
        int $userId,
        string $interval = 'daily',
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array;
}
