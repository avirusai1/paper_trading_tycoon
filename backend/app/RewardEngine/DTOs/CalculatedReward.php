<?php
declare(strict_types=1);

namespace App\RewardEngine\DTOs;

use App\RewardEngine\Enums\RewardType;

/**
 * Immutable result of the calculator stage.
 *
 * Contains the final amounts (after all multipliers) that will be persisted
 * by the distributor. No DB writes have occurred yet at this point.
 *
 * Amounts use the paise model: coin amounts are BIGINT paise (₹1 = 100 paise).
 * XP amounts are raw integer points.
 */
final readonly class CalculatedReward
{
    /**
     * @param  RewardType          $rewardType        What is being distributed.
     * @param  string              $idempotencyKey    Forwarded from the request.
     * @param  int                 $userId            Target player.
     * @param  int                 $baseXP            XP before multipliers.
     * @param  int                 $finalXP           XP after all multipliers applied.
     * @param  int                 $baseCoins         Coins (paise) before multipliers.
     * @param  int                 $finalCoins        Coins (paise) after all multipliers.
     * @param  float               $totalMultiplier   Effective combined multiplier (≥ 1.0).
     * @param  array<string,float> $multiplierBreakdown  Individual multipliers by name.
     * @param  array<string,mixed> $extras            Type-specific payload (item IDs, title, etc.)
     * @param  bool                $isDryRun          True if no writes should occur.
     */
    public function __construct(
        public readonly RewardType $rewardType,
        public readonly string     $idempotencyKey,
        public readonly int        $userId,
        public readonly int        $baseXP             = 0,
        public readonly int        $finalXP            = 0,
        public readonly int        $baseCoins          = 0,
        public readonly int        $finalCoins         = 0,
        public readonly float      $totalMultiplier    = 1.0,
        public readonly array      $multiplierBreakdown = [],
        public readonly array      $extras             = [],
        public readonly bool       $isDryRun           = false,
    ) {}

    public function hasXP(): bool
    {
        return $this->finalXP > 0;
    }

    public function hasCoins(): bool
    {
        return $this->finalCoins > 0;
    }

    public function hasExtras(): bool
    {
        return $this->extras !== [];
    }
}
