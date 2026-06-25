<?php
declare(strict_types=1);

namespace App\GameEngine\Events;

use App\Enums\AchievementTier;
use App\GameEngine\Enums\GameEventType;

/**
 * Raised when an achievement is newly unlocked for a user.
 */
final readonly class AchievementUnlockedEvent implements GameEvent
{
    public function __construct(
        private readonly int            $userId,
        public readonly int             $achievementId,
        public readonly string          $achievementKey,
        public readonly AchievementTier $tier,
    ) {}

    public function eventType(): GameEventType { return GameEventType::AchievementUnlocked; }
    public function userId(): int              { return $this->userId; }
    public function sourceId(): string         { return (string) $this->achievementId; }

    public function idempotencyKey(): string
    {
        return sprintf('AchievementUnlockedEvent:%d:%d', $this->userId, $this->achievementId);
    }
}
