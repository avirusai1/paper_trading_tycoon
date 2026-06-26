<?php

declare(strict_types=1);

namespace App\Portfolio\ValueObjects;

use InvalidArgumentException;

/**
 * Class PortfolioHealth
 *
 * Immutable value object representing a portfolio health score (0-100).
 */
final readonly class PortfolioHealth
{
    public function __construct(public int $score)
    {
        if ($this->score < 0 || $this->score > 100) {
            throw new InvalidArgumentException("Portfolio health score must be between 0 and 100. Got: {$this->score}");
        }
    }

    /**
     * Gets a label for portfolio health.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return match (true) {
            $this->score <= 35 => 'Unhealthy',
            $this->score <= 65 => 'Needs Attention',
            $this->score <= 85 => 'Healthy',
            default => 'Excellent',
        };
    }
}
