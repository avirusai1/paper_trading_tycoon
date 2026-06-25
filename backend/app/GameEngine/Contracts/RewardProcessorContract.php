<?php
declare(strict_types=1);

namespace App\GameEngine\Contracts;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\RewardResult;
use App\Enums\CoinTransactionSource;

/**
 * Contract for the Coin Reward processing subsystem.
 *
 * Responsibilities:
 * - Compute coin amounts from the Rules Engine.
 * - Delegate the actual ledger write to CoinLedgerService.
 * - Return a typed RewardResult.
 *
 * Does NOT dispatch domain events.
 */
interface RewardProcessorContract
{
    /**
     * Grant coins to the user identified in the GameContext.
     *
     * @param  string  $sourceId    Idempotency key tying this reward to its source entity.
     * @param  int     $coinAmount  Explicit amount; pass 0 to let the Rules Engine derive it.
     * @throws \App\GameEngine\Exceptions\RewardException
     */
    public function grantCoins(
        GameContext $context,
        CoinTransactionSource $source,
        string $sourceId,
        int $coinAmount = 0,
    ): RewardResult;
}
