<?php
declare(strict_types=1);

namespace App\RewardEngine\Exceptions;

use App\RewardEngine\Enums\ValidationFailureReason;

/**
 * Thrown when a reward request fails the validator chain.
 *
 * Carries the structured ValidationFailureReason so callers can branch on the
 * specific failure type without string-matching the message.
 */
final class RewardValidationException extends RewardEngineException
{
    public function __construct(
        public readonly ValidationFailureReason $reason,
        string                                  $message = '',
        ?\Throwable                             $previous = null,
    ) {
        parent::__construct(
            $message ?: $reason->value,
            $reason->value,
            422,
            $previous,
        );
    }

    public static function duplicate(string $idempotencyKey): self
    {
        return new self(
            ValidationFailureReason::Duplicate,
            "Reward already granted for idempotency key '{$idempotencyKey}'.",
        );
    }

    public static function featureDisabled(string $feature): self
    {
        return new self(
            ValidationFailureReason::FeatureDisabled,
            "Feature '{$feature}' is disabled — reward cannot be granted.",
        );
    }

    public static function premiumOnly(): self
    {
        return new self(
            ValidationFailureReason::PremiumOnly,
            'This reward is only available to premium users.',
        );
    }

    public static function dailyLimitHit(string $source): self
    {
        return new self(
            ValidationFailureReason::DailyLimitHit,
            "Daily reward limit reached for source '{$source}'.",
        );
    }

    public static function expired(string $detail = ''): self
    {
        return new self(
            ValidationFailureReason::Expired,
            "Reward has expired" . ($detail ? ": {$detail}" : '.'),
        );
    }

    public static function referralAbuse(int $userId): self
    {
        return new self(
            ValidationFailureReason::ReferralAbuse,
            "Referral reward for user {$userId} failed anti-abuse check.",
        );
    }

    public static function userBanned(int $userId): self
    {
        return new self(
            ValidationFailureReason::UserBanned,
            "User {$userId} is banned — rewards are suspended.",
        );
    }

    public static function invalidSeason(): self
    {
        return new self(
            ValidationFailureReason::InvalidSeason,
            'No active season found — season reward cannot be processed.',
        );
    }

    public static function rewardDisabled(string $type): self
    {
        return new self(
            ValidationFailureReason::RewardDisabled,
            "Reward type '{$type}' is currently disabled via feature flags.",
        );
    }
}
