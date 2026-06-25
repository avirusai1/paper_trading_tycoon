<?php
declare(strict_types=1);

namespace App\RewardEngine\Distributors;

use App\Enums\CoinTransactionSource;
use App\GameEngine\Contracts\CoinLedgerContract;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardDistributorContract;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\DistributionResult;
use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardStatus;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;

/**
 * Distributes coin rewards via CoinLedgerContract (Game Engine boundary).
 *
 * Maps RewardSource → CoinTransactionSource before calling ledger.credit().
 * Catches UniqueConstraintViolationException for silent duplicate handling.
 * All coin amounts are in paise (BIGINT UNSIGNED).
 */
final class CoinDistributor implements RewardDistributorContract
{
    public function __construct(
        private readonly CoinLedgerContract $coinLedger,
    ) {}

    public function distribute(CalculatedReward $reward, RewardContext $context): DistributionResult
    {
        if ($reward->finalCoins === 0 || $reward->isDryRun) {
            return new DistributionResult(
                rewardType:     $reward->rewardType,
                status:         RewardStatus::Skipped,
                idempotencyKey: $reward->idempotencyKey,
                userId:         $reward->userId,
                coinsBefore:    $context->coinBalance(),
                coinsAfter:     $context->coinBalance(),
            );
        }

        $balanceBefore = $this->coinLedger->getBalance($reward->userId);

        $sourceValue = $reward->extras['source'] ?? RewardSource::Admin->value;

        try {
            $balanceAfter = $this->coinLedger->credit(
                userId:      $reward->userId,
                amount:      $reward->finalCoins,
                source:      $this->mapSource($sourceValue),
                sourceId:    $reward->idempotencyKey,
                description: "Reward: {$reward->rewardType->value}",
            );
        } catch (UniqueConstraintViolationException) {
            Log::info('[RewardEngine:CoinDistributor] Duplicate coin credit — skipping', [
                'user_id' => $reward->userId,
                'key'     => $reward->idempotencyKey,
            ]);

            return DistributionResult::skipped($reward->rewardType, $reward->idempotencyKey, $reward->userId);
        }

        return new DistributionResult(
            rewardType:   $reward->rewardType,
            status:       RewardStatus::Distributed,
            idempotencyKey: $reward->idempotencyKey,
            userId:       $reward->userId,
            coinsGranted: $reward->finalCoins,
            coinsBefore:  $balanceBefore,
            coinsAfter:   $balanceAfter,
        );
    }

    public function rollback(string $idempotencyKey, RewardContext $context): DistributionResult
    {
        // Compensating debit — amount should be looked up from coin_transactions.
        // For now, issue a zero-debit to record the intent and log the event.
        // TODO: Query coin_transactions by source_id=idempotencyKey, get amount.
        Log::warning('[RewardEngine:CoinDistributor] Coin rollback — compensating debit placeholder', [
            'user_id'         => $context->userId(),
            'idempotency_key' => $idempotencyKey,
        ]);

        return new DistributionResult(
            rewardType:     \App\RewardEngine\Enums\RewardType::Coins,
            status:         RewardStatus::RolledBack,
            idempotencyKey: $idempotencyKey,
            userId:         $context->userId(),
        );
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function mapSource(string $sourceValue): CoinTransactionSource
    {
        return match ($sourceValue) {
            RewardSource::Achievement->value => CoinTransactionSource::Achievement,
            RewardSource::Mission->value     => CoinTransactionSource::Challenge,
            RewardSource::LevelUp->value     => CoinTransactionSource::LevelUp,
            RewardSource::Referral->value    => CoinTransactionSource::Referral,
            RewardSource::DailyLogin->value  => CoinTransactionSource::DailyLogin,
            RewardSource::Season->value      => CoinTransactionSource::SeasonReward,
            default                          => CoinTransactionSource::AdminGrant,
        };
    }
}
