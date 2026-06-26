<?php

declare(strict_types=1);

namespace App\MarketData\ValueObjects;

use InvalidArgumentException;

final readonly class Ticker
{
    public string $symbol;

    public function __construct(string $symbol)
    {
        $normalized = strtoupper(trim($symbol));
        if ($normalized === '') {
            throw new InvalidArgumentException('Ticker symbol cannot be empty');
        }
        $this->symbol = $normalized;
    }

    public function equals(self $other): bool
    {
        return $this->symbol === $other->symbol;
    }
}
