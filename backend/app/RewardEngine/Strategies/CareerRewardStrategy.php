<?php

declare(strict_types=1);

namespace App\RewardEngine\Strategies;

use App\Models\CareerTitle;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardStrategyContract;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\DTOs\StrategyResult;
use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Enums\RewardType;
use Illuminate\Support\Facades\Log;

/**
 * Strategy for unlocking career titles as rewards (e.g. level-up bonus).
 *
 * Looks up the CareerTitle appropriate for the user's current level.
 * If the user's career_title in UserLevel already matches, skips (idempotent).
 *
 * Required metadata key: 'career_title' (string) — the title to unlock.
 * If absent, falls back to CareerTitle::forLevel(context.currentLevel).
 */
final class CareerRewardStrategy implements RewardStrategyContract
{
    public function handles(): RewardType
    {
        return RewardType::CareerUnlock;
    }

    public function calculate(RewardRequest $request, RewardContext $context): CalculatedReward
    {
        $title = $request->meta('career_title')
            ?? CareerTitle::forLevel($context->currentLevel())?->title
            ?? null;

        return new CalculatedReward(
            rewardType: RewardType::CareerUnlock,
            idempotencyKey: $request->idempotencyKey,
            userId: $request->userId,
            extras: ['career_title' => $title],
            isDryRun: $request->dryRun,
        );
    }

    public function distribute(CalculatedReward $reward, RewardContext $context): StrategyResult
    {
        if ($reward->isDryRun) {
            return new StrategyResult(
                rewardType: $this->handles(),
                status: RewardStatus::Validated,
                idempotencyKey: $reward->idempotencyKey,
                userId: $reward->userId,
            );
        }

        $title = $reward->extras['career_title'] ?? null;

        if ($title === null) {
            // No title for this level — not an error, just skip
            return new StrategyResult(
                rewardType: $this->handles(),
                status: RewardStatus::Skipped,
                idempotencyKey: $reward->idempotencyKey,
                userId: $reward->userId,
            );
        }

        // Update career_title on user_levels via UserLevel model
        $context->userLevel->update(['career_title' => $title]);

        Log::info('[RewardEngine:CareerStrategy] Career title unlocked', [
            'user_id' => $reward->userId,
            'title' => $title,
        ]);

        return new StrategyResult(
            rewardType: $this->handles(),
            status: RewardStatus::Distributed,
            idempotencyKey: $reward->idempotencyKey,
            userId: $reward->userId,
            extras: ['career_title' => $title],
        );
    }

    public function rollback(string $idempotencyKey, RewardContext $context): StrategyResult
    {
        // Career titles are not revoked on rollback — only forward progression
        Log::info('[RewardEngine:CareerStrategy] Rollback requested — career titles are non-reversible', [
            'user_id' => $context->userId(),
            'idempotency_key' => $idempotencyKey,
        ]);

        return StrategyResult::rolledBack($this->handles(), $idempotencyKey, $context->userId());
    }
}
