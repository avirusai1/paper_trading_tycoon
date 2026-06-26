<?php

declare(strict_types=1);

namespace App\RewardEngine\Validators;

use App\Models\RewardHistory;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardValidatorContract;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Exceptions\RewardValidationException;
use Carbon\Carbon;

/**
 * Rejects rewards from sources that have a daily cap when the cap is reached.
 *
 * Cap values are fetched from reward_history (count of today's grants for this
 * user + source combination). The limit itself is enforced by the daily limit
 * stored in RewardRequest::metadata['daily_limit_count'] by the caller, or
 * falls back to the default of PHP_INT_MAX (unlimited).
 *
 * For per-source caps, callers that know the limit should pass it in metadata.
 */
final class DailyLimitValidator implements RewardValidatorContract
{
    public function validate(RewardRequest $request, RewardContext $context): void
    {
        if (! $request->source->hasDailyLimit()) {
            return;
        }

        if ($request->source->bypassesValidation()) {
            return;
        }

        $dailyLimit = (int) $request->meta('daily_limit_count', PHP_INT_MAX);

        if ($dailyLimit === PHP_INT_MAX) {
            // No limit configured — skip DB query.
            return;
        }

        $startOfDay = Carbon::now('Asia/Kolkata')->startOfDay()->utc();

        $todayCount = RewardHistory::query()
            ->where('user_id', $request->userId)
            ->where('source_type', $request->source->value)
            ->where('created_at', '>=', $startOfDay)
            ->count();

        if ($todayCount >= $dailyLimit) {
            throw RewardValidationException::dailyLimitHit($request->source->value);
        }
    }
}
