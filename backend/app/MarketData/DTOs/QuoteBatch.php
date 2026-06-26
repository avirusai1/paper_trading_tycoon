<?php

declare(strict_types=1);

namespace App\MarketData\DTOs;

use App\MarketData\ValueObjects\Ticker;

final readonly class QuoteBatch
{
    /** @var array<string, StockQuote> */
    private array $quotesMap;

    /**
     * @param  StockQuote[]  $quotes
     */
    public function __construct(public array $quotes)
    {
        $map = [];
        foreach ($quotes as $quote) {
            $map[$quote->ticker->symbol] = $quote;
        }
        $this->quotesMap = $map;
    }

    public function get(Ticker|string $ticker): ?StockQuote
    {
        $symbol = $ticker instanceof Ticker ? $ticker->symbol : strtoupper(trim($ticker));

        return $this->quotesMap[$symbol] ?? null;
    }

    public function has(Ticker|string $ticker): bool
    {
        $symbol = $ticker instanceof Ticker ? $ticker->symbol : strtoupper(trim($ticker));

        return isset($this->quotesMap[$symbol]);
    }
}
