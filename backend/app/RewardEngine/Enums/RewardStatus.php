<?php
declare(strict_types=1);

namespace App\RewardEngine\Enums;

/**
 * The lifecycle status of a reward distribution attempt.
 *
 * A reward moves through these states during pipeline execution:
 *   Pending → Validated → Calculated → Distributed → Recorded
 *
 * Failure at any stage produces Failed. A duplicate request produces Skipped.
 * RolledBack is set when a compensating transaction has been applied.
 */
enum RewardStatus: string
{
    case Pending      = 'pending';
    case Validated    = 'validated';
    case Calculated   = 'calculated';
    case Distributed  = 'distributed';
    case Recorded     = 'recorded';
    case Failed       = 'failed';
    case Skipped      = 'skipped';      // Duplicate detection
    case RolledBack   = 'rolled_back';

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Recorded,
            self::Failed,
            self::Skipped,
            self::RolledBack => true,
            default           => false,
        };
    }

    public function isSuccess(): bool
    {
        return $this === self::Recorded;
    }
}
