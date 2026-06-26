<?php

declare(strict_types=1);

namespace App\GameEngine\Events;

use App\GameEngine\Enums\GameEventType;

/**
 * Raised by the season-close scheduled job for each active user at season end.
 * The pipeline handles league promotion/demotion and reward distribution.
 */
final readonly class SeasonEndedEvent implements GameEvent
{
    public function __construct(
        private readonly int $userId,
        public readonly int $seasonId,
    ) {}

    public function eventType(): GameEventType
    {
        return GameEventType::SeasonEnded;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function sourceId(): string
    {
        return (string) $this->seasonId;
    }

    public function idempotencyKey(): string
    {
        return sprintf('SeasonEndedEvent:%d:%d', $this->userId, $this->seasonId);
    }
}
