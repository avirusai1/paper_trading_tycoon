<?php
declare(strict_types=1);

namespace App\GameEngine\Events;

use App\GameEngine\Enums\GameEventType;

/**
 * Raised when a user completes (but has not yet claimed) a mission.
 * This event triggers XP/coin rewards through the GameEngine pipeline.
 */
final readonly class MissionCompletedEvent implements GameEvent
{
    public function __construct(
        private readonly int $userId,
        public readonly int  $userMissionId,
        public readonly int  $missionId,
        public readonly string $missionKey,
    ) {}

    public function eventType(): GameEventType { return GameEventType::MissionCompleted; }
    public function userId(): int              { return $this->userId; }
    public function sourceId(): string         { return (string) $this->userMissionId; }

    public function idempotencyKey(): string
    {
        return sprintf('MissionCompletedEvent:%d:%d', $this->userId, $this->userMissionId);
    }
}
