<?php

declare(strict_types=1);

namespace App\Portfolio\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

/**
 * Class SnapshotGenerated
 *
 * Dispatched when a portfolio snapshot has been saved to the database.
 */
final class SnapshotGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $snapshotId,
        public readonly int $totalPortfolioValuePaise,
        public readonly Carbon $takenAt
    ) {}
}
