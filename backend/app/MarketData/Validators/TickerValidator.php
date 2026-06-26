<?php

declare(strict_types=1);

namespace App\MarketData\Validators;

use App\MarketData\Exceptions\MarketDataException;

final class TickerValidator
{
    public static function validate(string $ticker): void
    {
        if (empty(trim($ticker))) {
            throw new MarketDataException('Ticker symbol cannot be empty');
        }
        if (! preg_match('/^[A-Z0-9.\-]+$/i', $ticker)) {
            throw new MarketDataException("Invalid ticker symbol format: {$ticker}");
        }
    }
}
