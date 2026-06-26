<?php

declare(strict_types=1);

namespace App\GameEngine\Exceptions;

/**
 * Thrown when league progression operations fail.
 */
final class LeagueException extends GameEngineException
{
    public static function noLeagueForUser(int $userId, int $seasonId): self
    {
        return new self(
            "No league membership found for user {$userId} in season {$seasonId}.",
            'league_not_found',
        );
    }
}
