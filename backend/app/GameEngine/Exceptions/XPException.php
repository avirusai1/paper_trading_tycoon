<?php
declare(strict_types=1);

namespace App\GameEngine\Exceptions;

/**
 * Thrown when an XP grant operation fails (e.g. duplicate idempotency key
 * conflict not handled by the DB index, or negative XP amount).
 */
final class XPException extends GameEngineException
{
    public static function negativeAmount(int $amount): self
    {
        return new self(
            "XP grant amount must be positive, got {$amount}.",
            'xp_negative_amount',
        );
    }

    public static function idempotencyConflict(string $sourceId): self
    {
        return new self(
            "XP grant already recorded for source_id '{$sourceId}'.",
            'xp_idempotency_conflict',
        );
    }
}
