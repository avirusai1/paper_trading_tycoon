<?php

declare(strict_types=1);

namespace App\GameEngine\Events;

use App\GameEngine\Enums\GameEventType;

/**
 * Internal event emitted by the pipeline when a level-up is detected.
 * Used to trigger achievement evaluation for level-based achievements.
 */
final readonly class LevelUpEvent implements GameEvent
{
    public function __construct(
        private readonly int $userId,
        public readonly int $newLevel,
        public readonly string $xpSourceId,
    ) {}

    public function eventType(): GameEventType
    {
        return GameEventType::LevelUp;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function sourceId(): string
    {
        return "level_{$this->newLevel}_{$this->xpSourceId}";
    }

    public function idempotencyKey(): string
    {
        return sprintf('LevelUpEvent:%d:%d:%s', $this->userId, $this->newLevel, $this->xpSourceId);
    }
}
