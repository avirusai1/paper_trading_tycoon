<?php

declare(strict_types=1);

namespace App\GameEngine\Exceptions;

/**
 * Thrown when achievement evaluation or unlock operations fail.
 */
final class AchievementException extends GameEngineException
{
    public static function notFound(int $achievementId): self
    {
        return new self(
            "Achievement {$achievementId} not found.",
            'achievement_not_found',
        );
    }

    public static function alreadyUnlocked(int $userId, int $achievementId): self
    {
        return new self(
            "Achievement {$achievementId} already unlocked for user {$userId} (non-repeatable).",
            'achievement_already_unlocked',
        );
    }
}
