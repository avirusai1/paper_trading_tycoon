<?php

declare(strict_types=1);

namespace App\MarketData\Validators;

use App\MarketData\Exceptions\ProviderException;
use App\MarketData\Support\CircuitBreaker;

final class ProviderAvailabilityValidator
{
    public static function validate(string $providerName): void
    {
        $cb = new CircuitBreaker($providerName);
        if (! $cb->isAvailable()) {
            throw new ProviderException("Market data provider '{$providerName}' is currently unavailable (circuit open).");
        }
    }
}
