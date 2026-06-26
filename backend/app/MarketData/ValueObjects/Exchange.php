<?php

declare(strict_types=1);

namespace App\MarketData\ValueObjects;

use InvalidArgumentException;

final readonly class Exchange
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));
        if ($normalized !== 'NSE' && $normalized !== 'BSE') {
            throw new InvalidArgumentException("Invalid exchange: {$value}. Supported: NSE, BSE");
        }
        $this->value = $normalized;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
