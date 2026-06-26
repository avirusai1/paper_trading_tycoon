<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

use App\MarketData\ValueObjects\CompanyName;
use App\MarketData\ValueObjects\Exchange;
use App\MarketData\ValueObjects\Industry;
use App\MarketData\ValueObjects\Sector;
use App\MarketData\ValueObjects\Ticker;

final readonly class SearchResult
{
    public function __construct(
        public Ticker $ticker,
        public CompanyName $name,
        public Exchange $exchange,
        public string $isin,
        public ?Sector $sector = null,
        public ?Industry $industry = null
    ) {}
}
