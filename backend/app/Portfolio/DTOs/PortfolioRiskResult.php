<?php

declare(strict_types=1);

namespace App\Portfolio\DTOs;

/**
 * Class PortfolioRiskResult
 *
 * DTO for carrying calculated risk parameters.
 */
final readonly class PortfolioRiskResult
{
    /**
     * @param float $maxDrawdownPercent
     * @param float $volatility
     * @param string|null $largestPosition
     * @param array<string, int> $exposurePerStock Keyed by symbol
     * @param array<string, int> $exposurePerSector Keyed by sector name
     * @param float $cashRiskPercent
     * @param int $riskScore
     * @param int $healthScore
     */
    public function __construct(
        public float $maxDrawdownPercent,
        public float $volatility,
        public ?string $largestPosition,
        public array $exposurePerStock,
        public array $exposurePerSector,
        public float $cashRiskPercent,
        public int $riskScore,
        public int $healthScore
    ) {}
}
