<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

use App\MarketData\ValueObjects\Price;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;
use App\MarketData\ValueObjects\Volume;

final readonly class HistoricalBar
{
    public function __construct(
        public Ticker $ticker,
        public Price $open,
        public Price $high,
        public Price $low,
        public Price $close,
        public Volume $volume,
        public Timestamp $timestamp
    ) {}
}
