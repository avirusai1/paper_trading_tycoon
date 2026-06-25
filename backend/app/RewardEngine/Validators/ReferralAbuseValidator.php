<?php
declare(strict_types=1);

namespace App\RewardEngine\Validators;

use App\Models\Referral;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardValidatorContract;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Exceptions\RewardValidationException;
use Illuminate\Support\Facades\Log;

/**
 * Applies basic referral-abuse heuristics before granting referral rewards.
 *
 * Checks:
 * 1. The referral record exists and is in 'completed' status.
 * 2. The referred user has not been flagged for referral fraud.
 * 3. Referrer ≠ Referred (self-referral guard).
 *
 * More sophisticated ML-based scoring should be done asynchronously via
 * App\Jobs\FlagReferralFraud — this validator is a fast synchronous gate.
 *
 * Only runs for RewardSource::Referral sources.
 */
final class ReferralAbuseValidator implements RewardValidatorContract
{
    public function validate(RewardRequest $request, RewardContext $context): void
    {
        if (! $request->source->requiresReferralCheck()) {
            return;
        }

        $referralId = $request->meta('referral_id');
        $referrerId = $request->meta('referrer_id');

        if ($referralId === null) {
            throw RewardValidationException::referralAbuse($request->userId);
        }

        // Self-referral guard
        if ($referrerId !== null && (int) $referrerId === $request->userId) {
            Log::warning('[RewardEngine] Self-referral attempt blocked', [
                'user_id'     => $request->userId,
                'referral_id' => $referralId,
            ]);
            throw RewardValidationException::referralAbuse($request->userId);
        }

        // Verify referral record exists and is completed
        $referral = Referral::query()
            ->where('id', $referralId)
            ->where('referred_user_id', $request->userId)
            ->where('status', 'completed')
            ->first();

        if ($referral === null) {
            throw RewardValidationException::referralAbuse($request->userId);
        }

        // Check referred user is not flagged for fraud
        if ((bool) $referral->is_fraud_flagged) {
            Log::warning('[RewardEngine] Fraud-flagged referral blocked', [
                'user_id'     => $request->userId,
                'referral_id' => $referralId,
            ]);
            throw RewardValidationException::referralAbuse($request->userId);
        }
    }
}
