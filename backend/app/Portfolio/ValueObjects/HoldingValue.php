<?php

declare(strict_types=1);

namespace App\Portfolio\ValueObjects;

use App\Helpers\MoneyHelper;

/**
 * Class HoldingValue
 *
 * Immutable value object representing a holding position value in paise.
 */
final readonly class HoldingValue
{
    public function __construct(public int $valuePaise) {}

    /**
     * Converts to rupees.
     *
     * @return string
     */
    public function toRupees(): string
    {
        return MoneyHelper::paiseToRupees($this->valuePaise);
    }

    /**
     * Checks equality.
     *
     * @param HoldingValue $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->valuePaise === $other->valuePaise;
    }
}
