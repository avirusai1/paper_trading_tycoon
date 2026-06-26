<?php

declare(strict_types=1);

namespace App\GameEngine\Events;

use App\GameEngine\Enums\GameEventType;

/**
 * Raised when a referred user completes the onboarding requirement
 * (e.g. their first trade) and the referrer becomes eligible for a reward.
 */
final readonly class ReferralCompletedEvent implements GameEvent
{
    public function __construct(
        /** The referrer (user who shared the code). */
        private readonly int $userId,
        public readonly int $referralId,
        public readonly int $referredUserId,
    ) {}

    public function eventType(): GameEventType
    {
        return GameEventType::ReferralCompleted;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function sourceId(): string
    {
        return (string) $this->referralId;
    }

    public function idempotencyKey(): string
    {
        return sprintf('ReferralCompletedEvent:%d:%d', $this->userId, $this->referralId);
    }
}
