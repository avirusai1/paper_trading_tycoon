<?php

declare(strict_types=1);

namespace App\MarketData\ValueObjects;

use InvalidArgumentException;

final readonly class Volume
{
    public function __construct(public int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException("Volume cannot be negative: {$value}");
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
