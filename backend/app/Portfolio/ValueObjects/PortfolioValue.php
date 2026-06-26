<?php

declare(strict_types=1);

namespace App\Portfolio\ValueObjects;

use App\Helpers\MoneyHelper;

/**
 * Class PortfolioValue
 *
 * Immutable value object representing a portfolio value in paise.
 */
final readonly class PortfolioValue
{
    public function __construct(public int $valuePaise) {}

    /**
     * Converts the paise amount to rupees.
     *
     * @return string
     */
    public function toRupees(): string
    {
        return MoneyHelper::paiseToRupees($this->valuePaise);
    }

    /**
     * Checks equality with another PortfolioValue.
     *
     * @param PortfolioValue $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->valuePaise === $other->valuePaise;
    }
}
