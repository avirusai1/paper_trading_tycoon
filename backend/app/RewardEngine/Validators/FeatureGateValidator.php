<?php

declare(strict_types=1);

namespace App\RewardEngine\Validators;

use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardValidatorContract;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Exceptions\RewardValidationException;

/**
 * Rejects rewards when the feature gate for that reward type is disabled.
 *
 * Feature flag keys follow: 'rewards.enabled.{rewardType.value}'
 * e.g. 'rewards.enabled.xp', 'rewards.enabled.inventory_item'
 *
 * When the flag key is absent, defaults to ENABLED (fail-open for new types).
 */
final class FeatureGateValidator implements RewardValidatorContract
{
    public function validate(RewardRequest $request, RewardContext $context): void
    {
        if ($request->source->bypassesValidation()) {
            return;
        }

        $flagKey = 'rewards.enabled.'.$request->rewardType->value;

        // If the feature flag explicitly false, reject.
        // Missing key = enabled by default.
        if ($context->hasFeature($flagKey) === false
            && array_key_exists($flagKey, $context->featureFlags)) {
            throw RewardValidationException::rewardDisabled($request->rewardType->value);
        }
    }
}
