<?php

declare(strict_types=1);

namespace App\Trading\ValueObjects;

use InvalidArgumentException;

/**
 * Paper Trading Tycoon — Brokerage Value Object
 */
final readonly class Brokerage
{
    public function __construct(public int $valuePaise)
    {
        if ($valuePaise < 0) {
            throw new InvalidArgumentException("Brokerage in paise cannot be negative. Got: {$valuePaise}");
        }
    }

    public function equals(self $other): bool
    {
        return $this->valuePaise === $other->valuePaise;
    }
}
