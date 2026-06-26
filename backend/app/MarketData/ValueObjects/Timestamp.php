<?php

declare(strict_types=1);

namespace App\MarketData\ValueObjects;

use Carbon\Carbon;

final readonly class Timestamp
{
    public Carbon $value;

    public function __construct(Carbon|string|int $value)
    {
        if ($value instanceof Carbon) {
            $this->value = $value;
        } elseif (is_int($value)) {
            $this->value = Carbon::createFromTimestamp($value);
        } else {
            $this->value = Carbon::parse($value);
        }
    }

    public static function now(): self
    {
        return new self(Carbon::now());
    }

    public function format(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->value->format($format);
    }

    public function equals(self $other): bool
    {
        return $this->value->eq($other->value);
    }
}
