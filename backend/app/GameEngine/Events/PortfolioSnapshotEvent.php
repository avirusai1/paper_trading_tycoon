<?php
declare(strict_types=1);

namespace App\GameEngine\Events;

use App\GameEngine\Enums\GameEventType;

/**
 * Raised when a portfolio snapshot is taken (scheduled or on-demand).
 * Used by the League processor to update in-season standing.
 */
final readonly class PortfolioSnapshotEvent implements GameEvent
{
    public function __construct(
        private readonly int    $userId,
        private readonly string $snapshotId,
        public readonly int     $totalValuePaise,
    ) {}

    public function eventType(): GameEventType { return GameEventType::PortfolioSnapshot; }
    public function userId(): int              { return $this->userId; }
    public function sourceId(): string         { return $this->snapshotId; }

    public function idempotencyKey(): string
    {
        return sprintf('PortfolioSnapshotEvent:%d:%s', $this->userId, $this->snapshotId);
    }
}
