<?php

declare(strict_types=1);

namespace App\Portfolio\ValueObjects;

use App\Helpers\MoneyHelper;

/**
 * Class ProfitLoss
 *
 * Immutable value object representing a profit or loss.
 */
final readonly class ProfitLoss
{
    public function __construct(
        public int $absolutePaise,
        public float $percentage
    ) {}

    /**
     * Checks if this represents a profit.
     *
     * @return bool
     */
    public function isProfit(): bool
    {
        return $this->absolutePaise >= 0;
    }

    /**
     * Converts to rupees.
     *
     * @return string
     */
    public function absoluteToRupees(): string
    {
        return MoneyHelper::paiseToRupees($this->absolutePaise);
    }

    /**
     * Formats the percentage representation.
     *
     * @return string
     */
    public function formatPercentage(): string
    {
        $sign = $this->percentage >= 0 ? '+' : '';
        return sprintf('%s%.2f%%', $sign, $this->percentage);
    }
}
