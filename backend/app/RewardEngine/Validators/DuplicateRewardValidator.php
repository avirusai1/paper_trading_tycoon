<?php
declare(strict_types=1);

namespace App\RewardEngine\Validators;

use App\Models\RewardHistory;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardValidatorContract;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Exceptions\RewardValidationException;

/**
 * Rejects rewards whose idempotency key has already been recorded.
 *
 * Queries reward_history.source_id where source_type = rewardType.value.
 * Idempotent at DB level via UNIQUE(user_id, source_type, source_id), but
 * this validator provides a fast pre-check and a structured exception.
 */
final class DuplicateRewardValidator implements RewardValidatorContract
{
    public function validate(RewardRequest $request, RewardContext $context): void
    {
        // Admin bypasses duplicate check — allows re-grants
        if ($request->source->bypassesValidation()) {
            return;
        }

        $exists = RewardHistory::query()
            ->where('user_id', $request->userId)
            ->where('source_type', $request->rewardType->value)
            ->where('source_id', $request->idempotencyKey)
            ->exists();

        if ($exists) {
            throw RewardValidationException::duplicate($request->idempotencyKey);
        }
    }
}
