<?php

declare(strict_types=1);

namespace App\Portfolio\ValueObjects;

use App\Helpers\MoneyHelper;

/**
 * Class PortfolioReturn
 *
 * Immutable value object representing a portfolio return (absolute and percentage).
 */
final readonly class PortfolioReturn
{
    public function __construct(
        public int $absolutePaise,
        public float $percentage
    ) {}

    /**
     * Checks if the return is positive (gain).
     *
     * @return bool
     */
    public function isPositive(): bool
    {
        return $this->absolutePaise > 0;
    }

    /**
     * Converts absolute return to rupees.
     *
     * @return string
     */
    public function absoluteToRupees(): string
    {
        return MoneyHelper::paiseToRupees($this->absolutePaise);
    }

    /**
     * Formats the percentage return (e.g., "+5.23%" or "-2.10%").
     *
     * @return string
     */
    public function formatPercentage(): string
    {
        $sign = $this->percentage >= 0 ? '+' : '';
        return sprintf('%s%.2f%%', $sign, $this->percentage);
    }
}
