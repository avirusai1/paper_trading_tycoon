<?php

declare(strict_types=1);

namespace App\MarketData\ValueObjects;

final readonly class MarketCap
{
    public function __construct(public Price $price) {}

    public function equals(self $other): bool
    {
        return $this->price->equals($other->price);
    }
}
