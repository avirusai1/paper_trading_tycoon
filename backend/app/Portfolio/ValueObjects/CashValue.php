<?php

declare(strict_types=1);

namespace App\Portfolio\ValueObjects;

use App\Helpers\MoneyHelper;

/**
 * Class CashValue
 *
 * Immutable value object representing a cash amount in paise.
 */
final readonly class CashValue
{
    public function __construct(public int $valuePaise) {}

    /**
     * Converts to rupees representation.
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
     * @param CashValue $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->valuePaise === $other->valuePaise;
    }
}
