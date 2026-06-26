<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

use App\Enums\MarketStatus;
use App\MarketData\ValueObjects\Percentage;
use App\MarketData\ValueObjects\Price;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;
use App\MarketData\ValueObjects\Volume;

final readonly class StockQuote
{
    public function __construct(
        public Ticker $ticker,
        public Price $ltp,
        public Price $open,
        public Price $high,
        public Price $low,
        public Price $close,
        public Price $change,
        public Percentage $changePercent,
        public Volume $volume,
        public MarketStatus $marketStatus,
        public Timestamp $quotedAt
    ) {}
}
