<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

use App\MarketData\ValueObjects\Percentage;
use App\MarketData\ValueObjects\Sector;

final readonly class SectorPerformance
{
    public function __construct(
        public Sector $sector,
        public Percentage $changePercent
    ) {}
}
