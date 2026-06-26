<?php

declare(strict_types=1);

namespace App\GameEngine\Exceptions;

/**
 * Thrown when mission progress or reward claim operations fail.
 */
final class MissionException extends GameEngineException
{
    public static function notFound(int $userMissionId): self
    {
        return new self(
            "UserMission {$userMissionId} not found.",
            'mission_not_found',
        );
    }

    public static function notCompleted(int $userMissionId): self
    {
        return new self(
            "UserMission {$userMissionId} is not yet completed — cannot claim reward.",
            'mission_not_completed',
        );
    }

    public static function alreadyClaimed(int $userMissionId): self
    {
        return new self(
            "UserMission {$userMissionId} reward has already been claimed.",
            'mission_already_claimed',
        );
    }

    public static function expired(int $userMissionId): self
    {
        return new self(
            "UserMission {$userMissionId} has expired.",
            'mission_expired',
        );
    }
}
