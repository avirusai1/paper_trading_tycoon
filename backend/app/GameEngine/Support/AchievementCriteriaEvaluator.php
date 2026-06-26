<?php

declare(strict_types=1);

namespace App\GameEngine\Support;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\Events\GameEvent;
use App\GameEngine\Events\TradeExecutedEvent;
use App\Models\Achievement;
use App\Models\Trade;
use App\Models\UserMission;

/**
 * Evaluates whether a given achievement's criteria are satisfied by the
 * current GameContext (post-event state).
 *
 * Achievement criteria are stored as JSON in `achievements.criteria`.
 * Supported criterion types and their expected JSON structure:
 *
 * {"type": "trade_count",     "threshold": 10}           → user has made ≥ N trades
 * {"type": "level_reached",   "threshold": 10}           → user is at level ≥ N
 * {"type": "login_streak",    "threshold": 7}            → streak ≥ N days
 * {"type": "mission_count",   "threshold": 5}            → user has completed ≥ N missions
 * {"type": "portfolio_value", "threshold_paise": 50000000} → portfolio ≥ ₹5L
 * {"type": "first_trade"}                                → user has made their first trade
 */
final class AchievementCriteriaEvaluator
{
    /**
     * Determine whether the achievement's criteria are satisfied given the
     * current context and the event that triggered evaluation.
     *
     * Returns true if the achievement should be unlocked.
     */
    public function isSatisfied(Achievement $achievement, GameContext $context, GameEvent $event): bool
    {
        $criteria = $achievement->criteria ?? [];
        $type = $criteria['type'] ?? '';

        return match ($type) {
            'first_trade' => $event instanceof TradeExecutedEvent && $event->isFirstTrade,
            'level_reached' => $context->currentLevel() >= (int) ($criteria['threshold'] ?? PHP_INT_MAX),
            'login_streak' => $context->loginStreakDays >= (int) ($criteria['threshold'] ?? PHP_INT_MAX),
            'mission_count' => $this->completedMissionCount($context) >= (int) ($criteria['threshold'] ?? PHP_INT_MAX),
            'portfolio_value' => $context->wallet->virtual_cash_paise >= (int) ($criteria['threshold_paise'] ?? PHP_INT_MAX),
            'trade_count' => $this->tradeCount($context) >= (int) ($criteria['threshold'] ?? PHP_INT_MAX),
            default => false,
        };
    }

    private function completedMissionCount(GameContext $context): int
    {
        return UserMission::where('user_id', $context->userId())
            ->where('status', 'completed')
            ->count();
    }

    private function tradeCount(GameContext $context): int
    {
        return Trade::where('user_id', $context->userId())->count();
    }
}
