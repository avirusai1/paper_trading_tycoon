<?php
declare(strict_types=1);

namespace App\RewardEngine\Validators;

use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardValidatorContract;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Exceptions\RewardValidationException;

/**
 * Rejects non-premium users from receiving premium-only reward types.
 *
 * A reward is marked premium-only via RewardRequest::metadata['premium_only'] = true.
 * This lets callers mark specific grants as premium-gated without needing a new
 * enum case for every variation.
 */
final class PremiumOnlyValidator implements RewardValidatorContract
{
    public function validate(RewardRequest $request, RewardContext $context): void
    {
        if ($request->source->bypassesValidation()) {
            return;
        }

        $isPremiumOnly = (bool) $request->meta('premium_only', false);

        if ($isPremiumOnly && ! $context->isPremium) {
            throw RewardValidationException::premiumOnly();
        }
    }
}
