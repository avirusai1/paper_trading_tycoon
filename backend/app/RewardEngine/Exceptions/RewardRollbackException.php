<?php
declare(strict_types=1);

namespace App\RewardEngine\Exceptions;

/**
 * Thrown when a rollback attempt itself encounters an error.
 * This is a critical alert — requires admin intervention.
 */
final class RewardRollbackException extends RewardEngineException
{
    public static function partialRollback(int $userId, string $rewardId, string $detail): self
    {
        return new self(
            "Partial rollback for user {$userId} reward '{$rewardId}': {$detail}.",
            'reward_partial_rollback',
            500,
        );
    }
}
