<?php

declare(strict_types=1);

namespace App\GameEngine\Pipelines;

use App\Enums\CoinTransactionSource;
use App\Enums\OrderSide;
use App\GameEngine\Actions\GrantMissionProgressAction;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\AchievementResult;
use App\GameEngine\DTOs\CareerResult;
use App\GameEngine\DTOs\GameResult;
use App\GameEngine\DTOs\LeagueResult;
use App\GameEngine\DTOs\LevelResult;
use App\GameEngine\DTOs\MissionResult;
use App\GameEngine\DTOs\RewardResult;
use App\GameEngine\DTOs\SeasonResult;
use App\GameEngine\DTOs\XPResult;
use App\GameEngine\Enums\GameEventType;
use App\GameEngine\Enums\XPSource;
use App\GameEngine\Events\GameEvent;
use App\GameEngine\Events\LevelUpEvent;
use App\GameEngine\Events\PortfolioSnapshotEvent;
use App\GameEngine\Events\SeasonEndedEvent;
use App\GameEngine\Events\TradeExecutedEvent;
use App\GameEngine\Exceptions\LeagueException;
use App\GameEngine\Exceptions\MissionException;
use App\GameEngine\Exceptions\SeasonException;
use App\GameEngine\Processors\AchievementProcessor;
use App\GameEngine\Processors\CareerProcessor;
use App\GameEngine\Processors\LeagueProcessor;
use App\GameEngine\Processors\MissionProcessor;
use App\GameEngine\Processors\RewardProcessor;
use App\GameEngine\Processors\SeasonProcessor;
use App\GameEngine\Processors\XPProcessor;
use App\Models\Level;
use App\Models\Season;

/**
 * Orchestrates all game progression processors for a single gameplay event.
 *
 * Pipeline execution order (processors skipped if not applicable to event type):
 * 1. Guard — player must be able to participate
 * 2. XP — grant XP for the event source
 * 3. Level — check for level-up (embedded in XPResult)
 * 4. Career — update career title if level changed
 * 5. Missions — advance matching missions, collect completions
 * 6. Achievements — evaluate and unlock matching achievements
 * 7. League — update in-season portfolio standing (portfolio snapshot events)
 * 8. Season — handle enrollment / end-of-season rewards
 * 9. Rewards — grant level-up coin bonus
 *
 * The pipeline accumulates all results and returns a single GameResult.
 * No domain events are dispatched here — the caller (GameEngine service)
 * dispatches Laravel domain events after the pipeline completes.
 */
final class GameEventPipeline
{
    public function __construct(
        private readonly XPProcessor $xpProcessor,
        private readonly CareerProcessor $careerProcessor,
        private readonly MissionProcessor $missionProcessor,
        private readonly AchievementProcessor $achievementProcessor,
        private readonly LeagueProcessor $leagueProcessor,
        private readonly SeasonProcessor $seasonProcessor,
        private readonly RewardProcessor $rewardProcessor,
    ) {}

    public function execute(GameContext $context, GameEvent $event): GameResult
    {
        $startTime = microtime(true);

        /** @var XPResult[] $xpResults */
        /** @var LevelResult[] $levelResults */
        /** @var CareerResult[] $careerResults */
        /** @var MissionResult[] $missionResults */
        /** @var AchievementResult[] $achievementResults */
        /** @var LeagueResult[] $leagueResults */
        /** @var SeasonResult[] $seasonResults */
        /** @var RewardResult[] $rewardResults */
        $xpResults = [];
        $levelResults = [];
        $careerResults = [];
        $missionResults = [];
        $achievementResults = [];
        $leagueResults = [];
        $seasonResults = [];
        $rewardResults = [];

        // ── Guard ─────────────────────────────────────────────────────────────
        if (! $context->canParticipate()) {
            return $this->buildResult(
                $event, $context, $xpResults, $levelResults, $careerResults,
                $missionResults, $achievementResults, $leagueResults,
                $seasonResults, $rewardResults, $startTime,
            );
        }

        $eventType = $event->eventType();

        // ── Season enrollment (idempotent, runs early) ────────────────────────
        if ($eventType === GameEventType::UserRegistered) {
            try {
                $seasonResults[] = $this->seasonProcessor->ensureEnrolled($context);
            } catch (SeasonException) {
                // No active season — not a blocking error
            }
        }

        // ── Season end ────────────────────────────────────────────────────────
        if ($event instanceof SeasonEndedEvent) {
            $season = Season::find($event->sourceId());
            if ($season !== null) {
                $leagueResults[] = $this->leagueProcessor->processSeasonEnd($context, (int) $event->sourceId());
                $seasonResults[] = $this->seasonProcessor->distributeRewards($context, $season);
            }

            return $this->buildResult(
                $event, $context, $xpResults, $levelResults, $careerResults,
                $missionResults, $achievementResults, $leagueResults,
                $seasonResults, $rewardResults, $startTime,
            );
        }

        // ── Portfolio snapshot (league update only) ───────────────────────────
        if ($event instanceof PortfolioSnapshotEvent) {
            if ($context->currentLeague !== null) {
                try {
                    $leagueResults[] = $this->leagueProcessor->updateSeasonStanding(
                        $context,
                        $event->totalValuePaise,
                    );
                } catch (LeagueException) {
                    // Not enrolled yet — skip
                }
            }

            return $this->buildResult(
                $event, $context, $xpResults, $levelResults, $careerResults,
                $missionResults, $achievementResults, $leagueResults,
                $seasonResults, $rewardResults, $startTime,
            );
        }

        // ── XP stage ─────────────────────────────────────────────────────────
        if ($eventType->grantsXP()) {
            $xpSource = $this->resolveXPSource($event);

            if ($xpSource !== null) {
                $xpResult = $this->xpProcessor->grant($context, $xpSource, $event->sourceId());
                $xpResults[] = $xpResult;

                // ── Level stage (embedded in XP result) ───────────────────────
                if ($xpResult->didLevelUp) {
                    for ($lvl = $xpResult->levelBefore + 1; $lvl <= $xpResult->levelAfter; $lvl++) {
                        $levelRecord = Level::where('level_number', $lvl)->first();
                        $levelResults[] = new LevelResult(
                            userId: $context->userId(),
                            levelBefore: $lvl - 1,
                            levelAfter: $lvl,
                            careerTitleBefore: $context->userLevel->career_title,
                            careerTitleAfter: $context->userLevel->career_title,
                            coinReward: $levelRecord?->coin_reward ?? 0,
                            unlocks: $levelRecord?->unlocks ?? [],
                        );

                        // Level-up coin reward
                        if (($levelRecord?->coin_reward ?? 0) > 0) {
                            $rewardResults[] = $this->rewardProcessor->grantCoins(
                                $context,
                                CoinTransactionSource::LevelUp,
                                "level_up_{$lvl}_{$event->sourceId()}",
                                $levelRecord->coin_reward,
                            );
                        }
                    }

                    // ── Career stage ──────────────────────────────────────────
                    $careerResults[] = $this->careerProcessor->evaluate($context);

                    // ── Achievement evaluation for level-up ───────────────────
                    if ($eventType->triggersAchievements()) {
                        $levelUpEvent = new LevelUpEvent(
                            $context->userId(),
                            $xpResult->levelAfter,
                            $event->sourceId(),
                        );
                        $achievementResults = array_merge(
                            $achievementResults,
                            $this->achievementProcessor->evaluate($context, $levelUpEvent),
                        );
                    }
                }
            }
        }

        // ── Mission stage ─────────────────────────────────────────────────────
        if ($eventType->triggersMissions() && ! empty($context->activeMissions)) {
            $progressResults = GrantMissionProgressAction::class;
            // Delegate to MissionProcessor with the full event
            $missionResults = array_merge(
                $missionResults,
                $this->advanceMissionsWithEvent($context, $event),
            );
        }

        // ── Achievement stage ─────────────────────────────────────────────────
        if ($eventType->triggersAchievements()) {
            $achievementResults = array_merge(
                $achievementResults,
                $this->achievementProcessor->evaluate($context, $event),
            );
        }

        return $this->buildResult(
            $event, $context, $xpResults, $levelResults, $careerResults,
            $missionResults, $achievementResults, $leagueResults,
            $seasonResults, $rewardResults, $startTime,
        );
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function resolveXPSource(GameEvent $event): ?XPSource
    {
        return match ($event->eventType()) {
            GameEventType::TradeExecuted => $event instanceof TradeExecutedEvent && $event->isFirstTrade
                ? XPSource::FirstTrade
                : ($event->side === OrderSide::Buy ? XPSource::TradeBuy : XPSource::TradeSell),
            GameEventType::DailyLoginCompleted => XPSource::DailyLogin,
            GameEventType::MissionCompleted => XPSource::MissionCompleted,
            GameEventType::AchievementUnlocked => XPSource::AchievementUnlocked,
            GameEventType::ReferralCompleted => XPSource::ReferralJoined,
            GameEventType::SeasonEnded => XPSource::SeasonReward,
            default => null,
        };
    }

    /**
     * @return MissionResult[]
     */
    private function advanceMissionsWithEvent(GameContext $context, GameEvent $event): array
    {
        // Directly call the action rather than going through MissionProcessor::advance()
        // to pass the full typed event for accurate criteria evaluation
        $action = app(GrantMissionProgressAction::class);
        $results = $action->execute($context, $event);

        // Auto-grant rewards for newly completed missions
        $enriched = [];
        foreach ($results as $missionResult) {
            if ($missionResult->justCompleted) {
                try {
                    $claimed = $this->missionProcessor->claimReward($context, $missionResult->userMissionId);
                    $enriched[] = $claimed;
                } catch (MissionException) {
                    $enriched[] = $missionResult;
                }
            } else {
                $enriched[] = $missionResult;
            }
        }

        return $enriched;
    }

    private function buildResult(
        GameEvent $event,
        GameContext $context,
        array $xpResults,
        array $levelResults,
        array $careerResults,
        array $missionResults,
        array $achievementResults,
        array $leagueResults,
        array $seasonResults,
        array $rewardResults,
        float $startTime,
    ): GameResult {
        return new GameResult(
            triggerEvent: $event,
            userId: $context->userId(),
            xpResults: $xpResults,
            levelResults: $levelResults,
            careerResults: $careerResults,
            missionResults: $missionResults,
            achievementResults: $achievementResults,
            leagueResults: $leagueResults,
            seasonResults: $seasonResults,
            rewardResults: $rewardResults,
            processingTimeMs: round((microtime(true) - $startTime) * 1000, 2),
        );
    }
}
