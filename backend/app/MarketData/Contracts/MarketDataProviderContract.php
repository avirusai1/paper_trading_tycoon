<?php

declare(strict_types=1);

namespace App\MarketData\Contracts;

interface MarketDataProviderContract
{
    public function getName(): string;

    public function isHealthy(): bool;
}
