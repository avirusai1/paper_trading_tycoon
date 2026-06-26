<?php

declare(strict_types=1);

namespace App\MarketData\Validators;

use Carbon\Carbon;

final class MarketHoursValidator
{
    public static function isMarketOpen(?Carbon $time = null): bool
    {
        $now = $time ?? Carbon::now('Asia/Kolkata');

        if ($now->isWeekend()) {
            return false;
        }

        $timeStr = $now->format('H:i');

        return $timeStr >= '09:15' && $timeStr < '15:30';
    }
}
