<?php

declare(strict_types=1);

namespace App\Trading\ValueObjects;

use InvalidArgumentException;

/**
 * Paper Trading Tycoon — Quantity Value Object
 */
final readonly class Quantity
{
    public function __construct(public int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Quantity must be a positive integer. Got: {$value}");
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
