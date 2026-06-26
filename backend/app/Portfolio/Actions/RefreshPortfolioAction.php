<?php

declare(strict_types=1);

namespace App\Portfolio\Actions;

use App\Portfolio\Pipelines\RefreshPortfolioPipeline;
use App\Portfolio\DTOs\PortfolioResult;

/**
 * Class RefreshPortfolioAction
 *
 * Direct entry point for executing the full portfolio recalculation pipeline.
 */
final readonly class RefreshPortfolioAction
{
    public function __construct(
        private RefreshPortfolioPipeline $pipeline
    ) {}

    /**
     * Runs the refresh pipeline.
     *
     * @param int $userId
     * @param string $snapshotType
     * @return PortfolioResult
     */
    public function execute(int $userId, string $snapshotType = 'manual'): PortfolioResult
    {
        return $this->pipeline->execute($userId, $snapshotType);
    }
}
