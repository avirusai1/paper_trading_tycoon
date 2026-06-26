<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

use App\Enums\MarketStatus as EnumMarketStatus;
use App\MarketData\ValueObjects\Exchange;
use App\MarketData\ValueObjects\Timestamp;

final readonly class MarketStatus
{
    public function __construct(
        public Exchange $exchange,
        public EnumMarketStatus $status,
        public bool $isOpen,
        public ?Timestamp $nextOpening = null,
        public ?Timestamp $nextClosing = null
    ) {}
}
