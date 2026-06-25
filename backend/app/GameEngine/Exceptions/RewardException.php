<?php
declare(strict_types=1);

namespace App\GameEngine\Exceptions;

/**
 * Thrown when a coin reward operation fails (e.g. would result in
 * negative balance, duplicate credit attempt outside DB idempotency).
 */
final class RewardException extends GameEngineException
{
    public static function negativeBalance(int $userId, int $debit, int $balance): self
    {
        return new self(
            "Cannot debit {$debit} coins from user {$userId} — current balance is {$balance}.",
            'reward_insufficient_balance',
        );
    }

    public static function negativeAmount(int $amount): self
    {
        return new self(
            "Coin reward amount must be positive, got {$amount}.",
            'reward_negative_amount',
        );
    }
}
