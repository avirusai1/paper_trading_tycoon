<?php

declare(strict_types=1);

namespace App\RewardEngine\Validators;

use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardValidatorContract;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Exceptions\RewardValidationException;

/**
 * Rejects rewards for banned or suspended users.
 *
 * Ban status is resolved in RewardContextFactory and cached on RewardContext::isBanned.
 * Admin grants bypass this check so the admin panel can grant to any user.
 */
final class UserStatusValidator implements RewardValidatorContract
{
    public function validate(RewardRequest $request, RewardContext $context): void
    {
        if ($request->source->bypassesValidation()) {
            return;
        }

        if ($context->isBanned) {
            throw RewardValidationException::userBanned($request->userId);
        }
    }
}
