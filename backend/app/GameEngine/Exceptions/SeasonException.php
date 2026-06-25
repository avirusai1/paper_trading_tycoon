<?php
declare(strict_types=1);

namespace App\GameEngine\Exceptions;

/**
 * Thrown when season lifecycle operations fail (e.g. no active season,
 * reward distribution attempted on incomplete season).
 */
final class SeasonException extends GameEngineException
{
    public static function noActiveSeason(): self
    {
        return new self(
            'No active season found. Ensure a season record with status=active exists.',
            'season_no_active',
        );
    }

    public static function seasonNotEnded(int $seasonId): self
    {
        return new self(
            "Season {$seasonId} has not ended yet — rewards cannot be distributed.",
            'season_not_ended',
        );
    }

    public static function alreadyEnrolled(int $userId, int $seasonId): self
    {
        return new self(
            "User {$userId} is already enrolled in season {$seasonId}.",
            'season_already_enrolled',
        );
    }
}
