<?php

declare(strict_types=1);

namespace App\RewardEngine\Exceptions;

/**
 * Thrown when the calculator stage cannot determine a valid reward amount.
 * Examples: missing Rules Engine key, negative calculated amount.
 */
final class RewardCalculationException extends RewardEngineException
{
    public static function missingRule(string $key): self
    {
        return new self(
            "Cannot calculate reward — Rules Engine key '{$key}' not found.",
            'reward_calculation_missing_rule',
        );
    }

    public static function negativeAmount(string $rewardType, int|float $amount): self
    {
        return new self(
            "Calculated {$rewardType} reward amount is negative ({$amount}) — aborting.",
            'reward_calculation_negative_amount',
        );
    }
}
