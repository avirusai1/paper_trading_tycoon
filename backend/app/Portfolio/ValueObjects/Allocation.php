<?php

declare(strict_types=1);

namespace App\Portfolio\ValueObjects;

use App\Helpers\MoneyHelper;

/**
 * Class Allocation
 *
 * Immutable value object representing an asset or sector allocation.
 */
final readonly class Allocation
{
    public function __construct(
        public string $name,
        public int $valuePaise,
        public float $percentage
    ) {}

    /**
     * Converts value to rupees.
     *
     * @return string
     */
    public function valueToRupees(): string
    {
        return MoneyHelper::paiseToRupees($this->valuePaise);
    }

    /**
     * Formats percentage.
     *
     * @return string
     */
    public function formatPercentage(): string
    {
        return sprintf('%.2f%%', $this->percentage);
    }
}
