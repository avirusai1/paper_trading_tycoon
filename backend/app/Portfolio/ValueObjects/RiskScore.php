<?php

declare(strict_types=1);

namespace App\Portfolio\ValueObjects;

use InvalidArgumentException;

/**
 * Class RiskScore
 *
 * Immutable value object representing a risk score (0-100).
 */
final readonly class RiskScore
{
    public function __construct(public int $score)
    {
        if ($this->score < 0 || $this->score > 100) {
            throw new InvalidArgumentException("Risk score must be between 0 and 100. Got: {$this->score}");
        }
    }

    /**
     * Gets the risk label description.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return match (true) {
            $this->score <= 25 => 'Low',
            $this->score <= 50 => 'Moderate',
            $this->score <= 75 => 'High',
            default => 'Very High',
        };
    }
}
