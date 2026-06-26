<?php

declare(strict_types=1);

namespace App\Portfolio\DTOs;

use Carbon\Carbon;

/**
 * Class PortfolioPerformanceResult
 *
 * DTO carrying reconstructed portfolio performance over time.
 */
final readonly class PortfolioPerformanceResult
{
    /**
     * @param int $userId
     * @param string $interval
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array<int, array<string, mixed>> $performancePoints
     * @param int $absoluteReturnPaise
     * @param float $percentageReturn
     * @param float $compoundedReturnPercent
     */
    public function __construct(
        public int $userId,
        public string $interval,
        public Carbon $startDate,
        public Carbon $endDate,
        public array $performancePoints,
        public int $absoluteReturnPaise,
        public float $percentageReturn,
        public float $compoundedReturnPercent
    ) {}
}
