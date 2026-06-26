<?php

declare(strict_types=1);

namespace App\MarketData\ValueObjects;

use InvalidArgumentException;

final readonly class Sector
{
    public string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Sector name cannot be empty');
        }
        $this->value = $trimmed;
    }

    public function equals(self $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }
}
