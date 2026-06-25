<?php
declare(strict_types=1);

namespace App\Services\Economy;

use App\Enums\CoinTransactionSource;
use App\GameEngine\Contracts\CoinLedgerContract;
use App\GameEngine\Exceptions\RewardException;
use App\Models\CoinTransaction;
use App\Models\Wallet;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

/**
 * Concrete implementation of CoinLedgerContract.
 *
 * Provides append-only coin ledger operations per ADR-004.
 * Idempotency is enforced by the DB UNIQUE(user_id, source_type, source_id) index.
 *
 * The Game Engine uses this via CoinLedgerContract. The store/purchase
 * flow can inject this directly.
 */
final class CoinLedgerService extends BaseService implements CoinLedgerContract
{
    public function credit(
        int                   $userId,
        int                   $amount,
        CoinTransactionSource $source,
        string                $sourceId,
        ?string               $description = null,
    ): int {
        if ($amount <= 0) {
            throw RewardException::negativeAmount($amount);
        }

        return $this->transactional(function () use ($userId, $amount, $source, $sourceId, $description): int {
            $wallet       = Wallet::where('user_id', $userId)->lockForUpdate()->firstOrFail();
            $balanceAfter = $wallet->coin_balance + $amount;

            try {
                CoinTransaction::create([
                    'user_id'       => $userId,
                    'amount'        => $amount,
                    'source_type'   => $source,
                    'source_id'     => $sourceId,
                    'balance_after' => $balanceAfter,
                    'description'   => $description,
                ]);
            } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                // Already credited — return current balance
                return $wallet->coin_balance;
            }

            $wallet->update([
                'coin_balance'            => $balanceAfter,
                'coin_balance_updated_at' => now(),
            ]);

            return $balanceAfter;
        });
    }

    public function debit(
        int                   $userId,
        int                   $amount,
        CoinTransactionSource $source,
        string                $sourceId,
        ?string               $description = null,
    ): int {
        if ($amount <= 0) {
            throw RewardException::negativeAmount($amount);
        }

        return $this->transactional(function () use ($userId, $amount, $source, $sourceId, $description): int {
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->firstOrFail();

            if ($wallet->coin_balance < $amount) {
                throw RewardException::negativeBalance($userId, $amount, $wallet->coin_balance);
            }

            $balanceAfter = $wallet->coin_balance - $amount;

            CoinTransaction::create([
                'user_id'       => $userId,
                'amount'        => -$amount,
                'source_type'   => $source,
                'source_id'     => $sourceId,
                'balance_after' => $balanceAfter,
                'description'   => $description,
            ]);

            $wallet->update([
                'coin_balance'            => $balanceAfter,
                'coin_balance_updated_at' => now(),
            ]);

            return $balanceAfter;
        });
    }

    public function getBalance(int $userId): int
    {
        return (int) Wallet::where('user_id', $userId)->value('coin_balance');
    }
}
