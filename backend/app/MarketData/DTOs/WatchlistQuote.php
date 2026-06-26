<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

use App\MarketData\ValueObjects\CompanyName;
use App\MarketData\ValueObjects\Percentage;
use App\MarketData\ValueObjects\Price;
use App\MarketData\ValueObjects\Ticker;

final readonly class WatchlistQuote
{
    public function __construct(
        public Ticker $ticker,
        public CompanyName $name,
        public Price $ltp,
        public Price $change,
        public Percentage $changePercent
    ) {}
}
