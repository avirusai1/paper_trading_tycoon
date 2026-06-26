<?php

declare(strict_types=1);

namespace App\Trading\ValueObjects;

use InvalidArgumentException;

/**
 * Paper Trading Tycoon — Order Price Value Object
 */
final readonly class OrderPrice
{
    public function __construct(public int $valuePaise)
    {
        if ($valuePaise < 0) {
            throw new InvalidArgumentException("Order price in paise cannot be negative. Got: {$valuePaise}");
        }
    }

    public function equals(self $other): bool
    {
        return $this->valuePaise === $other->valuePaise;
    }
}
