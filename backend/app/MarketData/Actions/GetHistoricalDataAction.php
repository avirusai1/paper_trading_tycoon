<?php

declare(strict_types=1);

namespace App\MarketData\Actions;

use App\MarketData\Contracts\HistoricalDataProviderContract;
use App\MarketData\Contracts\StockRepositoryContract;
use App\MarketData\DTOs\HistoricalBar;
use App\MarketData\Support\ProviderCoordinator;
use App\MarketData\Validators\DateRangeValidator;
use App\MarketData\Validators\TickerValidator;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;
use Carbon\Carbon;

final class GetHistoricalDataAction
{
    public function __construct(
        private ProviderCoordinator $coordinator,
        private StockRepositoryContract $repository
    ) {}

    /**
     * @return HistoricalBar[]
     */
    public function execute(Ticker $ticker, Timestamp $startDate, Timestamp $endDate, string $interval = '1d'): array
    {
        TickerValidator::validate($ticker->symbol);
        DateRangeValidator::validate($startDate->value, $endDate->value);

        $dbBars = $this->repository->getHistoricalData($ticker, $startDate, $endDate);

        $expectedDays = $this->countWeekdays($startDate->value, $endDate->value);

        if (count($dbBars) >= $expectedDays) {
            return $dbBars;
        }

        $providerBars = $this->coordinator->execute(
            HistoricalDataProviderContract::class,
            fn (HistoricalDataProviderContract $provider) => $provider->getHistoricalData($ticker, $startDate, $endDate, $interval)
        );

        if (! empty($providerBars)) {
            $this->repository->saveHistoricalBars($providerBars);

            return $this->repository->getHistoricalData($ticker, $startDate, $endDate);
        }

        return $dbBars;
    }

    private function countWeekdays(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $curr = $start->copy();
        while ($curr->lte($end)) {
            if ($curr->isWeekday()) {
                $count++;
            }
            $curr->addDay();
        }

        return $count;
    }
}
