<?php

declare(strict_types=1);

namespace App\Trading\ValueObjects;

use InvalidArgumentException;

/**
 * Paper Trading Tycoon — Trade ID Value Object
 */
final readonly class TradeId
{
    public function __construct(public int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Trade ID must be a positive integer. Got: {$value}");
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
