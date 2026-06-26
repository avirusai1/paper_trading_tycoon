<?php

declare(strict_types=1);

namespace App\Portfolio\Repositories;

use App\Models\PortfolioSnapshot;
use App\Portfolio\Contracts\SnapshotRepositoryContract;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class SnapshotRepository
 *
 * Eloquent implementation of SnapshotRepositoryContract.
 */
final class SnapshotRepository implements SnapshotRepositoryContract
{
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
    ): PortfolioSnapshot {
        /** @var PortfolioSnapshot */
        return PortfolioSnapshot::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'snapshot_date' => $takenAt->toDateString(),
                'snapshot_type' => $type,
            ],
            [
                'virtual_cash_paise' => $cashPaise,
                'holdings_value_paise' => $holdingsValuePaise,
                'total_portfolio_value_paise' => $totalValuePaise,
                'total_pnl_paise' => $totalPnlPaise,
                'total_pnl_percent' => $totalPnlPercent,
                'total_holdings_count' => $holdingsCount,
                'taken_at' => $takenAt,
            ]
        );
    }

    public function getHistory(
        int $userId,
        array $types = ['daily', 'hourly', 'manual'],
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection {
        return PortfolioSnapshot::query()
            ->where('user_id', $userId)
            ->whereIn('snapshot_type', $types)
            ->when($startDate, fn($q) => $q->where('snapshot_date', '>=', $startDate->toDateString()))
            ->when($endDate, fn($q) => $q->where('snapshot_date', '<=', $endDate->toDateString()))
            ->orderBy('taken_at', 'asc')
            ->get();
    }
}
