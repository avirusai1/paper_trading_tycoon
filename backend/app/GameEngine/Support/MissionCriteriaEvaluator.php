<?php
declare(strict_types=1);

namespace App\GameEngine\Support;

use App\GameEngine\Events\GameEvent;
use App\GameEngine\Events\TradeExecutedEvent;
use App\GameEngine\Enums\MissionProgressType;
use App\Models\Mission;
use App\Models\UserMission;

/**
 * Determines which active missions should have their progress incremented
 * for a given game event, and by how much.
 *
 * Mission criteria are stored as JSON in `missions.criteria`.
 * Example criteria for a "buy 5 trades" mission:
 * {"type": "trade_buy", "min_value_paise": 0}
 *
 * This class encapsulates all criteria-matching logic so the MissionProcessor
 * stays focused on persistence and idempotency.
 */
final class MissionCriteriaEvaluator
{
    /**
     * Return the increment value for a given user mission given the current event.
     * Returns 0 if the mission's criteria are not matched by the event.
     */
    public function incrementFor(UserMission $userMission, GameEvent $event): int
    {
        $mission  = $userMission->mission;
        $criteria = $mission->criteria ?? [];
        $trigger  = MissionProgressType::tryFrom($criteria['type'] ?? '');

        if ($trigger === null) {
            return 0;
        }

        return match ($trigger) {
            MissionProgressType::Trade     => $this->evaluateTrade($event, $criteria),
            MissionProgressType::BuyTrade  => $this->evaluateBuyTrade($event, $criteria),
            MissionProgressType::SellTrade => $this->evaluateSellTrade($event, $criteria),
            MissionProgressType::Login     => $event instanceof \App\GameEngine\Events\DailyLoginEvent ? 1 : 0,
            MissionProgressType::Referral  => $event instanceof \App\GameEngine\Events\ReferralCompletedEvent ? 1 : 0,
            default                        => 0,
        };
    }

    private function evaluateTrade(GameEvent $event, array $criteria): int
    {
        if (! $event instanceof TradeExecutedEvent) {
            return 0;
        }
        $minValue = (int) ($criteria['min_value_paise'] ?? 0);
        return $event->totalValuePaise >= $minValue ? 1 : 0;
    }

    private function evaluateBuyTrade(GameEvent $event, array $criteria): int
    {
        if (! $event instanceof TradeExecutedEvent) {
            return 0;
        }
        if ($event->side !== \App\Enums\OrderSide::Buy) {
            return 0;
        }
        $minValue = (int) ($criteria['min_value_paise'] ?? 0);
        return $event->totalValuePaise >= $minValue ? 1 : 0;
    }

    private function evaluateSellTrade(GameEvent $event, array $criteria): int
    {
        if (! $event instanceof TradeExecutedEvent) {
            return 0;
        }
        if ($event->side !== \App\Enums\OrderSide::Sell) {
            return 0;
        }
        $minValue = (int) ($criteria['min_value_paise'] ?? 0);
        return $event->totalValuePaise >= $minValue ? 1 : 0;
    }
}
