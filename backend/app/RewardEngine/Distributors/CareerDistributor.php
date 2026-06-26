<?php

declare(strict_types=1);

namespace App\RewardEngine\Distributors;

use App\Models\CareerTitle;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardDistributorContract;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\DistributionResult;
use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Enums\RewardType;
use Illuminate\Support\Facades\Log;

/**
 * Distributes career title unlocks.
 *
 * Updates user_levels.career_title for the target user.
 * Idempotent: if the title already matches, returns Skipped.
 * Career titles are non-reversible on rollback (returns RolledBack status
 * but does not revert the title — by game design).
 */
final class CareerDistributor implements RewardDistributorContract
{
    public function distribute(CalculatedReward $reward, RewardContext $context): DistributionResult
    {
        if ($reward->isDryRun) {
            return new DistributionResult(
                rewardType: $reward->rewardType,
                status: RewardStatus::Skipped,
                idempotencyKey: $reward->idempotencyKey,
                userId: $reward->userId,
            );
        }

        $title = $reward->extras['career_title'] ?? null;

        if ($title === null) {
            $title = CareerTitle::forLevel($context->currentLevel())?->title;
        }

        if ($title === null) {
            return new DistributionResult(
                rewardType: $reward->rewardType,
                status: RewardStatus::Skipped,
                idempotencyKey: $reward->idempotencyKey,
                userId: $reward->userId,
            );
        }

        // Idempotent: already has this title
        if ($context->userLevel->career_title === $title) {
            return DistributionResult::skipped($reward->rewardType, $reward->idempotencyKey, $reward->userId);
        }

        $context->userLevel->update(['career_title' => $title]);

        Log::info('[RewardEngine:CareerDistributor] Career title updated', [
            'user_id' => $reward->userId,
            'title' => $title,
        ]);

        return new DistributionResult(
            rewardType: $reward->rewardType,
            status: RewardStatus::Distributed,
            idempotencyKey: $reward->idempotencyKey,
            userId: $reward->userId,
            extras: ['career_title' => $title],
        );
    }

    public function rollback(string $idempotencyKey, RewardContext $context): DistributionResult
    {
        Log::info('[RewardEngine:CareerDistributor] Career rollback — title is non-reversible', [
            'user_id' => $context->userId(),
        ]);

        return new DistributionResult(
            rewardType: RewardType::CareerUnlock,
            status: RewardStatus::RolledBack,
            idempotencyKey: $idempotencyKey,
            userId: $context->userId(),
        );
    }
}
