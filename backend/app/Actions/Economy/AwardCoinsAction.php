<?php

declare(strict_types=1);

namespace App\Actions\Economy;

use App\Enums\CoinTransactionSource;

/**
 * Paper Trading Tycoon — Award Coins Action
 *
 * Inserts an append-only coin ledger entry.
 * Enforces idempotency via source_type + source_id uniqueness.
 * Never updates a balance column directly — see ADR-004.
 * Implementation: Milestone 8.
 */
final class AwardCoinsAction
{
    public function execute(
        int $userId,
        int $amount,
        CoinTransactionSource $source,
        string $sourceId,
    ): void {
        // Implementation: Milestone 8
    }
}
