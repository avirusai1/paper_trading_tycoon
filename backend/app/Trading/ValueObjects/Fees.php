<?php

declare(strict_types=1);

namespace App\Trading\ValueObjects;

use InvalidArgumentException;

/**
 * Paper Trading Tycoon — Fees Value Object
 */
final readonly class Fees
{
    public function __construct(public int $valuePaise)
    {
        if ($valuePaise < 0) {
            throw new InvalidArgumentException("Fees in paise cannot be negative. Got: {$valuePaise}");
        }
    }

    public function equals(self $other): bool
    {
        return $this->valuePaise === $other->valuePaise;
    }
}
