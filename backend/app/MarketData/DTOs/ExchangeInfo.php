<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

use App\MarketData\ValueObjects\Currency;
use App\MarketData\ValueObjects\Exchange;

final readonly class ExchangeInfo
{
    public function __construct(
        public Exchange $exchange,
        public string $name,
        public string $timezone,
        public Currency $currency
    ) {}
}
