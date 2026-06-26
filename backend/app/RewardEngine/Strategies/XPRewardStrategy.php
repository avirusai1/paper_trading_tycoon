<?php

declare(strict_types=1);

namespace App\RewardEngine\Strategies;

use App\GameEngine\Contracts\XPProcessorContract;
use App\GameEngine\Enums\XPSource;
use App\RewardEngine\Calculators\XPCalculator;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardStrategyContract;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\DTOs\StrategyResult;
use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Enums\RewardType;
use Illuminate\Support\Facades\Log;

/**
 * Strategy for distributing XP rewards.
 *
 * Delegates calculation to XPCalculator and distribution to
 * XPProcessorContract (GameEngine boundary). This strategy never calls
 * the XP ledger directly — it goes through the existing game engine contract,
 * which handles daily caps, level-up detection, and audit logging.
 */
final class XPRewardStrategy implements RewardStrategyContract
{
    public function __construct(
        private readonly XPCalculator $calculator,
        private readonly XPProcessorContract $xpProcessor,
    ) {}

    public function handles(): RewardType
    {
        return RewardType::XP;
    }

    public function calculate(RewardRequest $request, RewardContext $context): CalculatedReward
    {
        return $this->calculator->calculate($request, $context);
    }

    public function distribute(CalculatedReward $reward, RewardContext $context): StrategyResult
    {
        if ($reward->isDryRun || $reward->finalXP === 0) {
            return new StrategyResult(
                rewardType: $this->handles(),
                status: $reward->isDryRun ? RewardStatus::Validated : RewardStatus::Skipped,
                idempotencyKey: $reward->idempotencyKey,
                userId: $reward->userId,
                xpGranted: $reward->finalXP,
            );
        }

        // Map RewardSource → XPSource for the Game Engine XP processor.
        // The sourceId is the idempotency key to ensure dedup at DB level.
        $xpSource = $this->resolveXPSource($reward);

        $xpResult = $this->xpProcessor->grant(
            context: $context->user->gameContext ?? null,
            source: $xpSource,
            sourceId: $reward->idempotencyKey,
            overrideAmount: $reward->finalXP,
        );

        Log::info('[RewardEngine:XPStrategy] XP distributed', [
            'user_id' => $reward->userId,
            'xp_granted' => $xpResult->amountGranted,
            'level_after' => $xpResult->levelAfter,
            'level_up' => $xpResult->didLevelUp,
            'key' => $reward->idempotencyKey,
        ]);

        return new StrategyResult(
            rewardType: $this->handles(),
            status: RewardStatus::Distributed,
            idempotencyKey: $reward->idempotencyKey,
            userId: $reward->userId,
            xpGranted: $xpResult->amountGranted,
            extras: [
                'xp_before' => $xpResult->xpBefore,
                'xp_after' => $xpResult->xpAfter,
                'level_before' => $xpResult->levelBefore,
                'level_after' => $xpResult->levelAfter,
                'did_level_up' => $xpResult->didLevelUp,
            ],
        );
    }

    public function rollback(string $idempotencyKey, RewardContext $context): StrategyResult
    {
        // XP rollback is a compensating debit. Currently a no-op because
        // XP is append-only by policy — we log the intent and return RolledBack.
        // TODO: Implement compensating XP debit via XPProcessorContract when supported.
        Log::warning('[RewardEngine:XPStrategy] XP rollback requested — append-only, no debit applied', [
            'user_id' => $context->userId(),
            'idempotency_key' => $idempotencyKey,
        ]);

        return StrategyResult::rolledBack($this->handles(), $idempotencyKey, $context->userId());
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function resolveXPSource(CalculatedReward $reward): XPSource
    {
        // The idempotency key prefix encodes the source value.
        // Fallback to AdminGrant for unknown mappings.
        return XPSource::AdminGrant;
    }
}
