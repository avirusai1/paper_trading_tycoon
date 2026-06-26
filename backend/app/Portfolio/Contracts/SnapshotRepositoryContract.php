<?php

declare(strict_types=1);

namespace App\Portfolio\Contracts;

use App\Models\PortfolioSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Interface SnapshotRepositoryContract
 *
 * Defines methods for storing and retrieving portfolio snapshots.
 */
interface SnapshotRepositoryContract
{
    /**
     * Saves a new portfolio snapshot in the database.
     *
     * @param int $userId
     * @param int $cashPaise
     * @param int $holdingsValuePaise
     * @param int $totalValuePaise
     * @param int $totalPnlPaise
     * @param float $totalPnlPercent
     * @param int $holdingsCount
     * @param string $type
     * @param Carbon $takenAt
     * @return PortfolioSnapshot
     */
    public function save(
        int $userId,
        int $cashPaise,
        int $holdingsValuePaise,
        int $totalValuePaise,
        int $totalPnlPaise,
        float $totalPnlPercent,
        int $holdingsCount,
        string $type,
        Carbon $takenAt
    ): PortfolioSnapshot;

    /**
     * Fetches historical snapshots within a date range.
     *
     * @param int $userId
     * @param array<string> $types
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return Collection<int, PortfolioSnapshot>
     */
    public function getHistory(
        int $userId,
        array $types = ['daily', 'hourly', 'manual'],
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection;
}
