<?php

declare(strict_types=1);

namespace App\MarketData\ValueObjects;

final readonly class Percentage
{
    public function __construct(public float $value) {}

    public function format(): string
    {
        return sprintf('%.2f%%', $this->value);
    }

    public function equals(self $other): bool
    {
        return abs($this->value - $other->value) < 0.00001;
    }
}
