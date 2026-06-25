<?php
declare(strict_types=1);

namespace App\GameEngine\Contracts;

use App\Enums\CoinTransactionSource;

/**
 * Internal contract for the Coin Ledger — used by RewardProcessor to persist
 * coin grants without depending on the concrete CoinLedgerService class.
 *
 * This thin interface isolates the Game Engine from the Economy service layer.
 */
interface CoinLedgerContract
{
    /**
     * Credit coins to a user.  Idempotent — duplicate (user, source, sourceId)
     * combinations are silently ignored (DB UNIQUE index enforces this).
     *
     * Returns the new materialized coin balance.
     *
     * @throws \App\GameEngine\Exceptions\RewardException  On negative balance after debit.
     */
    public function credit(
        int $userId,
        int $amount,
        CoinTransactionSource $source,
        string $sourceId,
        ?string $description = null,
    ): int;

    /**
     * Debit coins from a user.
     *
     * @throws \App\GameEngine\Exceptions\RewardException  If balance would go negative.
     */
    public function debit(
        int $userId,
        int $amount,
        CoinTransactionSource $source,
        string $sourceId,
        ?string $description = null,
    ): int;

    /**
     * Return the user's current coin balance (materialized cache).
     */
    public function getBalance(int $userId): int;
}
