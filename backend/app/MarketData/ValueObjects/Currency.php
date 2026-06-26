<?php

declare(strict_types=1);

namespace App\MarketData\ValueObjects;

use InvalidArgumentException;

final readonly class Currency
{
    public function __construct(public string $code)
    {
        if (strlen($code) !== 3) {
            throw new InvalidArgumentException("Currency code must be exactly 3 characters: {$code}");
        }
    }

    public function equals(self $other): bool
    {
        return strtoupper($this->code) === strtoupper($other->code);
    }
}
