<?php

declare(strict_types=1);

namespace App\Portfolio\DTOs;

/**
 * Class PortfolioAnalyticsResult
 *
 * DTO for portfolio-wide trading statistics and diversification breakdown.
 */
final readonly class PortfolioAnalyticsResult
{
    /**
     * @param int $totalTrades
     * @param int $winningTrades
     * @param int $losingTrades
     * @param float $winRate
     * @param array<string, mixed>|null $largestWinner Array with keys: symbol, amount_paise
     * @param array<string, mixed>|null $largestLoser Array with keys: symbol, amount_paise
     * @param string|null $bestStock
     * @param string|null $worstStock
     * @param float $averageHoldingPeriodSeconds
     * @param float $averageReturnPercent
     * @param int $currentExposurePaise
     * @param array<string, float> $sectorAllocation Keyed by sector name -> percentage
     * @param int $diversificationScore
     * @param float $portfolioConcentration
     * @param float $cashAllocation
     */
    public function __construct(
        public int $totalTrades,
        public int $winningTrades,
        public int $losingTrades,
        public float $winRate,
        public ?array $largestWinner,
        public ?array $largestLoser,
        public ?string $bestStock,
        public ?string $worstStock,
        public float $averageHoldingPeriodSeconds,
        public float $averageReturnPercent,
        public int $currentExposurePaise,
        public array $sectorAllocation,
        public int $diversificationScore,
        public float $portfolioConcentration,
        public float $cashAllocation
    ) {}
}
