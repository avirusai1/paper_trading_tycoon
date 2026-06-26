<?php

declare(strict_types=1);

namespace App\Trading\ValueObjects;

use InvalidArgumentException;

/**
 * Paper Trading Tycoon — Execution Price Value Object
 */
final readonly class ExecutionPrice
{
    public function __construct(public int $valuePaise)
    {
        if ($valuePaise <= 0) {
            throw new InvalidArgumentException("Execution price in paise must be a positive integer. Got: {$valuePaise}");
        }
    }

    public function equals(self $other): bool
    {
        return $this->valuePaise === $other->valuePaise;
    }
}
