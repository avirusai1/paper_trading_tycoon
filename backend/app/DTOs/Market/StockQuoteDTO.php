<?php

declare(strict_types=1);

namespace App\DTOs\Market;

use Carbon\Carbon;

/**
 * Paper Trading Tycoon — Stock Quote Data Transfer Object
 *
 * Normalized stock quote returned by the MarketDataService.
 * All prices are in paise (int). See ADR-004.
 */
final readonly class StockQuoteDTO
{
    public function __construct(
        public readonly string $symbol,
        public readonly string $name,
        public readonly int $pricePaise,
        public readonly int $openPaise,
        public readonly int $highPaise,
        public readonly int $lowPaise,
        public readonly int $previousClosePaise,
        public readonly int $changePaise,
        public readonly float $changePercent,
        public readonly int $volume,
        public readonly Carbon $quotedAt,
    ) {}

    /**
     * Whether the price has gone up from previous close.
     */
    public function isGain(): bool
    {
        return $this->changePaise >= 0;
    }
}
