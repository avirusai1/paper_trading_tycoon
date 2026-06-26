<?php

declare(strict_types=1);

namespace App\MarketData\Validators;

use App\MarketData\Exceptions\MarketDataException;
use Carbon\Carbon;

final class DateRangeValidator
{
    public static function validate(Carbon|string $startDate, Carbon|string $endDate): void
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        if ($start->gt($end)) {
            throw new MarketDataException("Start date ({$start->toDateString()}) cannot be after end date ({$end->toDateString()}).");
        }

        if ($start->gt(Carbon::now())) {
            throw new MarketDataException('Start date cannot be in the future.');
        }
    }
}
