<?php
declare(strict_types=1);

namespace App\RewardEngine\Actions;

use App\Models\RewardHistory;
use App\RewardEngine\DTOs\DistributionResult;
use App\RewardEngine\DTOs\RewardRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;

/**
 * Action: write the unified reward_history audit record.
 *
 * Called after all distributors succeed. reward_history is append-only
 * (no UPDATED_AT) — idempotency is handled by catching
 * UniqueConstraintViolationException if a race produces a duplicate
 * before this action runs.
 */
final class RecordRewardHistoryAction
{
    /**
     * Write to reward_history. Silently no-ops on duplicate source_id.
     *
     * @param  DistributionResult[]  $distributionResults
     */
    public function execute(RewardRequest $request, array $distributionResults): void
    {
        $totalXP    = array_sum(array_map(fn ($r) => $r->xpGranted, $distributionResults));
        $totalCoins = array_sum(array_map(fn ($r) => $r->coinsGranted, $distributionResults));

        try {
            RewardHistory::create([
                'user_id'     => $request->userId,
                'source_type' => $request->rewardType->value,
                'source_id'   => $request->idempotencyKey,
                'xp_amount'   => $totalXP,
                'coin_amount' => $totalCoins,
                'description' => "Reward: {$request->source->value} [{$request->sourceId}]",
            ]);

            Log::info('[RewardEngine:RecordHistory] Reward history recorded', [
                'user_id'     => $request->userId,
                'source_type' => $request->rewardType->value,
                'source_id'   => $request->idempotencyKey,
                'xp'          => $totalXP,
                'coins'       => $totalCoins,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Duplicate — already recorded (race condition); safe to ignore.
            Log::info('[RewardEngine:RecordHistory] Duplicate history entry silently skipped', [
                'user_id'    => $request->userId,
                'source_id'  => $request->idempotencyKey,
            ]);
        }
    }
}
