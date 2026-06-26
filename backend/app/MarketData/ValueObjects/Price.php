<?php

declare(strict_types=1);

namespace App\MarketData\ValueObjects;

use App\Helpers\MoneyHelper;
use InvalidArgumentException;

final readonly class Price
{
    public function __construct(public int $valuePaise)
    {
        if ($valuePaise < 0) {
            throw new InvalidArgumentException("Price in paise cannot be negative: {$valuePaise}");
        }
    }

    public static function fromRupees(float|string $rupees): self
    {
        return new self(MoneyHelper::rupeesToPaise($rupees));
    }

    public function toRupees(): string
    {
        return MoneyHelper::paiseToRupees($this->valuePaise);
    }

    public function add(self $other): self
    {
        return new self(MoneyHelper::add($this->valuePaise, $other->valuePaise));
    }

    public function subtract(self $other): self
    {
        return new self(MoneyHelper::subtract($this->valuePaise, $other->valuePaise));
    }

    public function multiply(int $quantity): self
    {
        return new self(MoneyHelper::multiply($this->valuePaise, $quantity));
    }

    public function equals(self $other): bool
    {
        return $this->valuePaise === $other->valuePaise;
    }
}
