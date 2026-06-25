<?php
declare(strict_types=1);

namespace App\GameEngine\Events;

use App\GameEngine\Enums\GameEventType;

/**
 * Raised once per calendar day when a user logs in or opens the app.
 * The login streak is computed from user_profiles.login_streak_days.
 */
final readonly class DailyLoginEvent implements GameEvent
{
    public function __construct(
        private readonly int $userId,
        /** YYYY-MM-DD string for the login date, used as sourceId for idempotency. */
        private readonly string $loginDate,
        public readonly int     $streakDays,
    ) {}

    public function eventType(): GameEventType { return GameEventType::DailyLoginCompleted; }
    public function userId(): int              { return $this->userId; }
    public function sourceId(): string         { return $this->loginDate; }

    public function idempotencyKey(): string
    {
        return sprintf('DailyLoginEvent:%d:%s', $this->userId, $this->loginDate);
    }
}
