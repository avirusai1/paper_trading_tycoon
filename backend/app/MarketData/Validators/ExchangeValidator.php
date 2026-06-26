<?php

declare(strict_types=1);

namespace App\MarketData\Validators;

use App\MarketData\Exceptions\MarketDataException;

final class ExchangeValidator
{
    public static function validate(string $exchange): void
    {
        $normalized = strtoupper(trim($exchange));
        if ($normalized !== 'NSE' && $normalized !== 'BSE') {
            throw new MarketDataException("Unsupported stock exchange: {$exchange}. Must be NSE or BSE.");
        }
    }
}
