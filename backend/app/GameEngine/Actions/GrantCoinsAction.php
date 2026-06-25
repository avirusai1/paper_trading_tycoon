<?php
declare(strict_types=1);

namespace App\GameEngine\Actions;

use App\Enums\CoinTransactionSource;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\RewardResult;
use App\GameEngine\Exceptions\RewardException;
use App\Models\CoinTransaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

/**
 * Atomically credits or debits coins from a user's wallet.
 *
 * Implements the append-only coin ledger pattern (ADR-004):
 * - Inserts into coin_transactions (never updates).
 * - UNIQUE(user_id, source_type, source_id) prevents duplicate grants.
 * - Materializes the new balance into wallets.coin_balance.
 *
 * Does NOT dispatch domain events.
 */
final class GrantCoinsAction
{
    /**
     * @throws RewardException
     */
    public function credit(
        GameContext           $context,
        CoinTransactionSource $source,
        string                $sourceId,
        int                   $amount,
        ?string               $description = null,
    ): RewardResult {
        if ($amount <= 0) {
            throw RewardException::negativeAmount($amount);
        }

        return DB::transaction(function () use ($context, $source, $sourceId, $amount, $description): RewardResult {
            $wallet       = Wallet::where('user_id', $context->userId())->lockForUpdate()->firstOrFail();
            $balanceBefore = $wallet->coin_balance;
            $balanceAfter  = $balanceBefore + $amount;

            try {
                CoinTransaction::create([
                    'user_id'      => $context->userId(),
                    'amount'       => $amount,
                    'source_type'  => $source,
                    'source_id'    => $sourceId,
                    'balance_after'=> $balanceAfter,
                    'description'  => $description,
                ]);
            } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                // Idempotent — already credited, reconstruct result from existing record
                $existing = CoinTransaction::where('user_id', $context->userId())
                    ->where('source_type', $source)
                    ->where('source_id', $sourceId)
                    ->firstOrFail();

                return new RewardResult(
                    userId:        $context->userId(),
                    coinsGranted:  $existing->amount,
                    balanceBefore: $existing->balance_after - $existing->amount,
                    balanceAfter:  $existing->balance_after,
                    source:        $source->value,
                    sourceId:      $sourceId,
                );
            }

            $wallet->update([
                'coin_balance'            => $balanceAfter,
                'coin_balance_updated_at' => now(),
            ]);

            return new RewardResult(
                userId:        $context->userId(),
                coinsGranted:  $amount,
                balanceBefore: $balanceBefore,
                balanceAfter:  $balanceAfter,
                source:        $source->value,
                sourceId:      $sourceId,
            );
        });
    }

    /**
     * @throws RewardException
     */
    public function debit(
        GameContext           $context,
        CoinTransactionSource $source,
        string                $sourceId,
        int                   $amount,
        ?string               $description = null,
    ): RewardResult {
        if ($amount <= 0) {
            throw RewardException::negativeAmount($amount);
        }

        return DB::transaction(function () use ($context, $source, $sourceId, $amount, $description): RewardResult {
            $wallet        = Wallet::where('user_id', $context->userId())->lockForUpdate()->firstOrFail();
            $balanceBefore = $wallet->coin_balance;

            if ($balanceBefore < $amount) {
                throw RewardException::negativeBalance($context->userId(), $amount, $balanceBefore);
            }

            $balanceAfter = $balanceBefore - $amount;

            CoinTransaction::create([
                'user_id'       => $context->userId(),
                'amount'        => -$amount, // negative = debit
                'source_type'   => $source,
                'source_id'     => $sourceId,
                'balance_after' => $balanceAfter,
                'description'   => $description,
            ]);

            $wallet->update([
                'coin_balance'            => $balanceAfter,
                'coin_balance_updated_at' => now(),
            ]);

            return new RewardResult(
                userId:        $context->userId(),
                coinsGranted:  -$amount,
                balanceBefore: $balanceBefore,
                balanceAfter:  $balanceAfter,
                source:        $source->value,
                sourceId:      $sourceId,
            );
        });
    }
}
