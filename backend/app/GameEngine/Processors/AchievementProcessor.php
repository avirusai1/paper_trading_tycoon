<?php

declare(strict_types=1);

namespace App\GameEngine\Processors;

use App\Enums\CoinTransactionSource;
use App\GameEngine\Actions\GrantAchievementProgressAction;
use App\GameEngine\Actions\GrantCoinsAction;
use App\GameEngine\Actions\GrantXPAction;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\Contracts\AchievementProcessorContract;
use App\GameEngine\DTOs\AchievementResult;
use App\GameEngine\Enums\XPSource;
use App\GameEngine\Events\GameEvent;

/**
 * Implements AchievementProcessorContract.
 *
 * After unlocking achievements, auto-grants their XP and coin rewards
 * through the standard action layer (full idempotency via coin ledger).
 */
final class AchievementProcessor implements AchievementProcessorContract
{
    public function __construct(
        private readonly GrantAchievementProgressAction $achievementAction,
        private readonly GrantXPAction $grantXP,
        private readonly GrantCoinsAction $grantCoins,
    ) {}

    /**
     * @return AchievementResult[]
     */
    public function evaluate(GameContext $context, GameEvent $event): array
    {
        $unlocked = $this->achievementAction->execute($context, $event);
        $results = [];

        foreach ($unlocked as $result) {
            if (! $result->justUnlocked) {
                $results[] = $result;
                continue;
            }

            $sourceId = "achievement_{$result->achievementId}_{$result->unlockCount}";

            // Grant XP
            if ($result->xpReward > 0) {
                $this->grantXP->execute(
                    $context,
                    XPSource::AchievementUnlocked,
                    $sourceId,
                    $result->xpReward,
                );
            }

            // Grant coins
            if ($result->coinReward > 0) {
                $this->grantCoins->credit(
                    $context,
                    CoinTransactionSource::Achievement,
                    $sourceId,
                    $result->coinReward,
                    "Achievement unlocked: {$result->achievementName}",
                );
            }

            $results[] = $result;
        }

        return $results;
    }
}
