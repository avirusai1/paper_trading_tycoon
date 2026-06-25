<?php
declare(strict_types=1);

namespace App\GameEngine\DTOs;

/**
 * Immutable result of a coin reward operation.
 */
final readonly class RewardResult
{
    public function __construct(
        public readonly int    $userId,
        public readonly int    $coinsGranted,
        public readonly int    $balanceBefore,
        public readonly int    $balanceAfter,
        /** CoinTransactionSource enum backing value. */
        public readonly string $source,
        public readonly string $sourceId,
    ) {}
}
