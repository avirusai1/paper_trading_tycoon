<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

use App\MarketData\ValueObjects\Price;
use App\MarketData\ValueObjects\Ticker;
use App\MarketData\ValueObjects\Timestamp;

final readonly class CorporateAction
{
    public function __construct(
        public Ticker $ticker,
        public string $type,
        public Timestamp $executionDate,
        public string $details,
        public ?Price $amount = null,
        public ?string $ratio = null
    ) {}
}
