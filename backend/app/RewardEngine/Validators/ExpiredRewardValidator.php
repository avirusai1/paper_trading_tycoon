<?php
declare(strict_types=1);

namespace App\RewardEngine\Validators;

use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardValidatorContract;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Exceptions\RewardValidationException;
use Carbon\Carbon;

/**
 * Rejects rewards that carry an expiry timestamp that has already passed.
 *
 * Callers set expiry via RewardRequest::metadata['expires_at'] (ISO 8601 string).
 * When absent, the reward does not expire.
 */
final class ExpiredRewardValidator implements RewardValidatorContract
{
    public function validate(RewardRequest $request, RewardContext $context): void
    {
        if ($request->source->bypassesValidation()) {
            return;
        }

        $expiresAt = $request->meta('expires_at');

        if ($expiresAt === null) {
            return;
        }

        $expiry = Carbon::parse($expiresAt);

        if ($expiry->isPast()) {
            throw RewardValidationException::expired("expired at {$expiry->toIso8601String()}");
        }
    }
}
