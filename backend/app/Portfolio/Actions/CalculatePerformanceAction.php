<?php

declare(strict_types=1);

namespace App\Portfolio\Actions;

use App\Portfolio\Contracts\SnapshotRepositoryContract;
use App\Portfolio\DTOs\PortfolioPerformanceResult;
use App\Portfolio\Calculators\ReturnCalculator;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class CalculatePerformanceAction
 *
 * Performs historical reconstruction and performance scaling calculations.
 */
final readonly class CalculatePerformanceAction
{
    public function __construct(
        private SnapshotRepositoryContract $snapshotRepository,
        private ReturnCalculator $returnCalculator
    ) {}

    /**
     * Reconstructs portfolio history for a specific range and interval.
     *
     * @param int $userId
     * @param string $interval Interval: daily, weekly, monthly, yearly
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return PortfolioPerformanceResult
     */
    public function execute(
        int $userId,
        string $interval = 'daily',
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): PortfolioPerformanceResult {
        $startDate = $startDate ?? now()->subMonth();
        $endDate = $endDate ?? now();

        // Retrieve snapshots for user
        $rawSnapshots = $this->snapshotRepository->getHistory($userId, ['daily', 'hourly', 'manual'], $startDate, $endDate);

        // Group and filter based on the interval
        $filteredSnapshots = $this->filterByInterval($rawSnapshots, $interval);

        $performancePoints = [];
        $firstVal = null;
        $lastVal = null;

        foreach ($filteredSnapshots as $snap) {
            $val = $snap->total_portfolio_value_paise;
            if ($firstVal === null) {
                $firstVal = $val;
            }
            $lastVal = $val;

            $performancePoints[] = [
                'date' => $snap->snapshot_date->toDateString(),
                'taken_at' => $snap->taken_at ? $snap->taken_at->toDateTimeString() : null,
                'cash_paise' => $snap->virtual_cash_paise,
                'holdings_value_paise' => $snap->holdings_value_paise,
                'total_value_paise' => $val,
                'pnl_paise' => $snap->total_pnl_paise,
                'pnl_percent' => (float) $snap->total_pnl_percent,
                'holdings_count' => $snap->total_holdings_count,
            ];
        }

        // Return metrics
        $absoluteReturn = ($lastVal ?? 0) - ($firstVal ?? 0);
        $percentageReturn = ($firstVal !== null && $firstVal > 0) 
            ? (float) (($absoluteReturn * 100) / $firstVal) 
            : 0.0;

        // CAGR compounded return
        $daysDiff = $startDate->diffInDays($endDate);
        $years = $daysDiff / 365.0;
        $cagr = $this->returnCalculator->compoundedReturn($percentageReturn, $years);

        return new PortfolioPerformanceResult(
            userId: $userId,
            interval: $interval,
            startDate: $startDate,
            endDate: $endDate,
            performancePoints: $performancePoints,
            absoluteReturnPaise: $absoluteReturn,
            percentageReturn: $percentageReturn,
            compoundedReturnPercent: $cagr
        );
    }

    /**
     * Filters snapshots based on interval grouping (last snapshot of each period).
     */
    private function filterByInterval(Collection $snapshots, string $interval): Collection
    {
        if ($snapshots->isEmpty()) {
            return new Collection();
        }

        $sorted = $snapshots->sortBy(function ($s) {
            return $s->taken_at ? $s->taken_at->timestamp : $s->created_at->timestamp;
        });

        return match (strtolower($interval)) {
            'weekly' => $this->groupByPeriod($sorted, fn($s) => $s->snapshot_date->format('Y-W')),
            'monthly' => $this->groupByPeriod($sorted, fn($s) => $s->snapshot_date->format('Y-m')),
            'yearly' => $this->groupByPeriod($sorted, fn($s) => $s->snapshot_date->format('Y')),
            default => $sorted,
        };
    }

    private function groupByPeriod(Collection $sorted, callable $grouper): Collection
    {
        $grouped = $sorted->groupBy($grouper);
        $result = new Collection();
        
        foreach ($grouped as $period => $items) {
            // Take the last snapshot in this period
            $result->push($items->last());
        }

        return $result;
    }
}
