<?php

declare(strict_types=1);

namespace App\Portfolio\Actions;

use App\Models\PortfolioSnapshot;
use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\Contracts\SnapshotRepositoryContract;
use App\Portfolio\Validators\SnapshotValidator;
use App\Portfolio\Events\SnapshotGenerated;
use Carbon\Carbon;

/**
 * Class GenerateSnapshotAction
 *
 * Validates, records, and dispatches events for a point-in-time portfolio snapshot.
 */
final readonly class GenerateSnapshotAction
{
    public function __construct(
        private SnapshotValidator $validator,
        private SnapshotRepositoryContract $snapshotRepository
    ) {}

    /**
     * Executes snapshot generation.
     *
     * @param PortfolioContext $context
     * @param int $cashPaise
     * @param int $holdingsValuePaise
     * @param int $totalValuePaise
     * @param int $totalPnlPaise
     * @param float $totalPnlPercent
     * @param string $type
     * @return PortfolioSnapshot
     */
    public function execute(
        PortfolioContext $context,
        int $cashPaise,
        int $holdingsValuePaise,
        int $totalValuePaise,
        int $totalPnlPaise,
        float $totalPnlPercent,
        string $type = 'manual'
    ): PortfolioSnapshot {
        // Run validations
        $this->validator->validate($context);

        $takenAt = Carbon::now();

        $snapshot = $this->snapshotRepository->save(
            $context->userId(),
            $cashPaise,
            $holdingsValuePaise,
            $totalValuePaise,
            $totalPnlPaise,
            $totalPnlPercent,
            $context->holdingsCount(),
            $type,
            $takenAt
        );

        // Dispatch snapshot generated event
        SnapshotGenerated::dispatch(
            $context->userId(),
            (int) $snapshot->id,
            $totalValuePaise,
            $takenAt
        );

        return $snapshot;
    }
}
