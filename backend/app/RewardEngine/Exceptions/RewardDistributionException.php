<?php
declare(strict_types=1);

namespace App\RewardEngine\Exceptions;

/**
 * Thrown when a distributor fails to persist a reward after calculation.
 * Triggers rollback of all previously distributed reward components.
 */
final class RewardDistributionException extends RewardEngineException
{
    public static function insufficientBalance(int $userId, int $required, int $actual): self
    {
        return new self(
            "Cannot debit coins from user {$userId}: requires {$required}, has {$actual}.",
            'reward_distribution_insufficient_balance',
        );
    }

    public static function itemUnavailable(int $storeItemId): self
    {
        return new self(
            "Store item {$storeItemId} is unavailable for reward distribution.",
            'reward_distribution_item_unavailable',
        );
    }

    public static function rollbackFailed(string $detail): self
    {
        return new self(
            "Reward rollback failed: {$detail}. Manual reconciliation required.",
            'reward_rollback_failed',
            500,
        );
    }
}
