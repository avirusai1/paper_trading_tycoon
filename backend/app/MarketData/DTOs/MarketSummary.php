<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

final readonly class MarketSummary
{
    /**
     * @param  StockQuote[]  $gainers
     * @param  StockQuote[]  $losers
     * @param  StockQuote[]  $active
     * @param  StockQuote[]  $trending
     * @param  SectorPerformance[]  $sectorPerformances
     */
    public function __construct(
        public array $gainers,
        public array $losers,
        public array $active,
        public array $trending,
        public array $sectorPerformances
    ) {}
}
