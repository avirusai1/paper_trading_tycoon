<?php

declare(strict_types=1);

namespace App\RewardEngine\Strategies;

use App\Enums\CoinTransactionSource;
use App\GameEngine\Contracts\CoinLedgerContract;
use App\RewardEngine\Calculators\CoinCalculator;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardStrategyContract;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\DTOs\StrategyResult;
use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Enums\RewardType;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;

/**
 * Strategy for distributing coin rewards via the CoinLedger (Game Engine boundary).
 *
 * Maps RewardSource → CoinTransactionSource before calling credit().
 * Catches UniqueConstraintViolationException to handle duplicate idempotency
 * keys silently (DB-level idempotency guard).
 */
final class CoinRewardStrategy implements RewardStrategyContract
{
    public function __construct(
        private readonly CoinCalculator $calculator,
        private readonly CoinLedgerContract $coinLedger,
    ) {}

    public function handles(): RewardType
    {
        return RewardType::Coins;
    }

    public function calculate(RewardRequest $request, RewardContext $context): CalculatedReward
    {
        return $this->calculator->calculate($request, $context);
    }

    public function distribute(CalculatedReward $reward, RewardContext $context): StrategyResult
    {
        if ($reward->isDryRun || $reward->finalCoins === 0) {
            return new StrategyResult(
                rewardType: $this->handles(),
                status: $reward->isDryRun ? RewardStatus::Validated : RewardStatus::Skipped,
                idempotencyKey: $reward->idempotencyKey,
                userId: $reward->userId,
                coinsGranted: $reward->finalCoins,
            );
        }

        $balanceBefore = $this->coinLedger->getBalance($reward->userId);

        try {
            $balanceAfter = $this->coinLedger->credit(
                userId: $reward->userId,
                amount: $reward->finalCoins,
                source: $this->resolveCoinSource($reward->extras['source'] ?? RewardSource::Mission->value),
                sourceId: $reward->idempotencyKey,
                description: "Reward: {$reward->rewardType->value} ({$reward->idempotencyKey})",
            );
        } catch (UniqueConstraintViolationException) {
            // Already credited — idempotent no-op
            Log::info('[RewardEngine:CoinStrategy] Duplicate idempotency key, skipping credit', [
                'user_id' => $reward->userId,
                'key' => $reward->idempotencyKey,
            ]);

            return new StrategyResult(
                rewardType: $this->handles(),
                status: RewardStatus::Skipped,
                idempotencyKey: $reward->idempotencyKey,
                userId: $reward->userId,
                wasIdempotent: true,
            );
        }

        Log::info('[RewardEngine:CoinStrategy] Coins credited', [
            'user_id' => $reward->userId,
            'coins_paise' => $reward->finalCoins,
            'balance_after' => $balanceAfter,
            'key' => $reward->idempotencyKey,
        ]);

        return new StrategyResult(
            rewardType: $this->handles(),
            status: RewardStatus::Distributed,
            idempotencyKey: $reward->idempotencyKey,
            userId: $reward->userId,
            coinsGranted: $reward->finalCoins,
            extras: [
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
            ],
        );
    }

    public function rollback(string $idempotencyKey, RewardContext $context): StrategyResult
    {
        // Compensating debit to reverse the credit.
        // Source ID is suffixed with ':rollback' to avoid duplicate key with the original.
        try {
            $this->coinLedger->debit(
                userId: $context->userId(),
                amount: 0, // Amount looked up from original ledger entry — TODO: query coin_transactions
                source: CoinTransactionSource::Refund,
                sourceId: $idempotencyKey.':rollback',
                description: "Rollback reward: {$idempotencyKey}",
            );
        } catch (\Throwable $e) {
            Log::error('[RewardEngine:CoinStrategy] Rollback debit failed', [
                'user_id' => $context->userId(),
                'idempotency_key' => $idempotencyKey,
                'error' => $e->getMessage(),
            ]);
        }

        return StrategyResult::rolledBack($this->handles(), $idempotencyKey, $context->userId());
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function resolveCoinSource(string $sourceValue): CoinTransactionSource
    {
        return match ($sourceValue) {
            RewardSource::Achievement->value => CoinTransactionSource::Achievement,
            RewardSource::Mission->value => CoinTransactionSource::Challenge,
            RewardSource::LevelUp->value => CoinTransactionSource::LevelUp,
            RewardSource::Referral->value => CoinTransactionSource::Referral,
            RewardSource::DailyLogin->value => CoinTransactionSource::DailyLogin,
            RewardSource::Season->value => CoinTransactionSource::SeasonReward,
            RewardSource::Admin->value => CoinTransactionSource::AdminGrant,
            default => CoinTransactionSource::AdminGrant,
        };
    }
}
