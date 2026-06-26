<?php

declare(strict_types=1);

namespace App\Portfolio\ValueObjects;

use InvalidArgumentException;

/**
 * Class DiversificationScore
 *
 * Immutable value object representing a diversification score (0-100).
 */
final readonly class DiversificationScore
{
    public function __construct(public int $score)
    {
        if ($this->score < 0 || $this->score > 100) {
            throw new InvalidArgumentException("Diversification score must be between 0 and 100. Got: {$this->score}");
        }
    }

    /**
     * Gets a label representation.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return match (true) {
            $this->score <= 30 => 'Highly Concentrated',
            $this->score <= 60 => 'Moderately Concentrated',
            $this->score <= 85 => 'Well Diversified',
            default => 'Highly Diversified',
        };
    }
}
