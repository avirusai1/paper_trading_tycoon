<?php
declare(strict_types=1);

namespace App\RewardEngine\Distributors;

use App\GameEngine\Contracts\XPProcessorContract;
use App\GameEngine\Enums\XPSource;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardDistributorContract;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\DistributionResult;
use App\RewardEngine\Enums\RewardStatus;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;

/**
 * Distributes XP rewards via the XPProcessorContract (Game Engine boundary).
 *
 * Does not write to xp_logs directly. All XP writes go through the
 * existing XPProcessor to ensure level-up detection, daily cap enforcement,
 * and the existing audit trail remain intact.
 */
final class XPDistributor implements RewardDistributorContract
{
    public function __construct(
        private readonly XPProcessorContract $xpProcessor,
    ) {}

    public function distribute(CalculatedReward $reward, RewardContext $context): DistributionResult
    {
        if ($reward->finalXP === 0 || $reward->isDryRun) {
            return new DistributionResult(
                rewardType:     $reward->rewardType,
                status:         RewardStatus::Skipped,
                idempotencyKey: $reward->idempotencyKey,
                userId:         $reward->userId,
                xpBefore:       $context->currentXP(),
                xpAfter:        $context->currentXP(),
            );
        }

        try {
            $xpResult = $this->xpProcessor->grant(
                context:        null,
                source:         XPSource::AdminGrant,
                sourceId:       $reward->idempotencyKey,
                overrideAmount: $reward->finalXP,
            );
        } catch (UniqueConstraintViolationException) {
            Log::info('[RewardEngine:XPDistributor] Duplicate — skipping XP grant', [
                'user_id' => $reward->userId,
                'key'     => $reward->idempotencyKey,
            ]);

            return DistributionResult::skipped($reward->rewardType, $reward->idempotencyKey, $reward->userId);
        }

        return new DistributionResult(
            rewardType:     $reward->rewardType,
            status:         RewardStatus::Distributed,
            idempotencyKey: $reward->idempotencyKey,
            userId:         $reward->userId,
            xpGranted:      $xpResult->amountGranted,
            xpBefore:       $xpResult->xpBefore,
            xpAfter:        $xpResult->xpAfter,
            extras:         [
                'level_before' => $xpResult->levelBefore,
                'level_after'  => $xpResult->levelAfter,
                'did_level_up' => $xpResult->didLevelUp,
            ],
        );
    }

    public function rollback(string $idempotencyKey, RewardContext $context): DistributionResult
    {
        // XP is append-only — no debit. Log and return RolledBack status.
        Log::warning('[RewardEngine:XPDistributor] XP rollback requested — XP is append-only', [
            'user_id'         => $context->userId(),
            'idempotency_key' => $idempotencyKey,
        ]);

        return new DistributionResult(
            rewardType:     \App\RewardEngine\Enums\RewardType::XP,
            status:         RewardStatus::RolledBack,
            idempotencyKey: $idempotencyKey,
            userId:         $context->userId(),
        );
    }
}
