<?php
declare(strict_types=1);

namespace App\GameEngine;

use App\Events\AchievementUnlocked;
use App\Events\CoinsAwarded;
use App\Events\LevelUp;
use App\Events\SeasonRewardGranted;
use App\Events\XPGranted;
use App\GameEngine\Contracts\GameEngineContract;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\GameResult;
use App\GameEngine\Events\GameEvent;
use App\GameEngine\Exceptions\GameEngineException;
use App\GameEngine\Factories\GameContextBuilder;
use App\GameEngine\Pipelines\GameEventPipeline;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Central Game Engine service — the single entry point for all gameplay
 * event processing.
 *
 * Responsibilities:
 * 1. Build the full GameContext for the affected user.
 * 2. Delegate to GameEventPipeline to execute all processing stages.
 * 3. Publish resulting Laravel domain events (App\Events\*) so that
 *    listeners can perform async work (notifications, analytics, etc.).
 * 4. Return the complete GameResult to the caller.
 *
 * This class is completely independent of HTTP. It has no knowledge of
 * controllers, requests, or responses. It may be called from:
 * - Queued event listeners (App\Listeners\*)
 * - Artisan commands (season close job, mission refresh)
 * - Integration tests
 *
 * It is registered as a singleton in AppServiceProvider so the
 * GameContextBuilder and pipeline processors are shared across calls
 * within the same request/job cycle.
 */
final class GameEngine implements GameEngineContract
{
    public function __construct(
        private readonly GameContextBuilder $contextBuilder,
        private readonly GameEventPipeline  $pipeline,
    ) {}

    /**
     * @throws GameEngineException
     */
    public function process(GameEvent $event): GameResult
    {
        $startTime = microtime(true);

        Log::info('[GameEngine] Processing event', [
            'event_type'      => $event->eventType()->value,
            'user_id'         => $event->userId(),
            'idempotency_key' => $event->idempotencyKey(),
        ]);

        try {
            $context = $this->contextBuilder->build($event->userId());
            $result  = $this->pipeline->execute($context, $event);

            $this->publishDomainEvents($result);

            $elapsed = round((microtime(true) - $startTime) * 1000, 1);

            Log::info('[GameEngine] Event processed', [
                'user_id'          => $event->userId(),
                'event_type'       => $event->eventType()->value,
                'xp_gained'        => $result->totalXPGranted(),
                'coins_gained'     => $result->totalCoinsGranted(),
                'level_up'         => $result->didLevelUp(),
                'missions_done'    => $result->missionsCompleted(),
                'achievements_done'=> $result->achievementsUnlocked(),
                'pipeline_ms'      => $result->processingTimeMs,
                'total_ms'         => $elapsed,
            ]);

            return $result;
        } catch (GameEngineException $e) {
            Log::error('[GameEngine] Processing failed', [
                'user_id'    => $event->userId(),
                'event_type' => $event->eventType()->value,
                'error'      => $e->errorCode(),
                'message'    => $e->getMessage(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error('[GameEngine] Unexpected error', [
                'user_id'    => $event->userId(),
                'event_type' => $event->eventType()->value,
                'exception'  => $e::class,
                'message'    => $e->getMessage(),
            ]);
            throw new GameEngineException(
                "Unexpected error processing game event: {$e->getMessage()}",
                'game_engine_unexpected_error',
                $e,
            );
        }
    }

    /**
     * @throws GameEngineException
     */
    public function buildContext(int $userId): GameContext
    {
        return $this->contextBuilder->build($userId);
    }

    // ── Domain event publishing ───────────────────────────────────────────────

    /**
     * Publish Laravel domain events after the pipeline completes.
     *
     * These are the application-layer events (App\Events\*) that feed
     * queued listeners for notifications, analytics, anti-cheat, etc.
     * All constructors are populated here now that Phase 3 provides
     * the GameResult payload.
     */
    private function publishDomainEvents(GameResult $result): void
    {
        // XP granted events
        foreach ($result->xpResults as $xpResult) {
            if ($xpResult->amountGranted > 0) {
                XPGranted::dispatch(
                    $xpResult->userId,
                    $xpResult->amountGranted,
                    $xpResult->source,
                    $xpResult->sourceId,
                );
            }
        }

        // Level-up events
        foreach ($result->levelResults as $levelResult) {
            if ($levelResult->levelsGained() > 0) {
                LevelUp::dispatch(
                    $levelResult->userId,
                    $levelResult->levelAfter,
                    $levelResult->careerTitleAfter,
                    $levelResult->unlocks,
                );
            }
        }

        // Coins awarded events
        foreach ($result->rewardResults as $rewardResult) {
            if ($rewardResult->coinsGranted > 0) {
                CoinsAwarded::dispatch(
                    $rewardResult->userId,
                    $rewardResult->coinsGranted,
                    $rewardResult->source,
                    $rewardResult->sourceId,
                );
            }
        }

        // Achievement unlocked events
        foreach ($result->achievementResults as $achievementResult) {
            if ($achievementResult->justUnlocked) {
                AchievementUnlocked::dispatch(
                    $achievementResult->userId,
                    $achievementResult->achievementId,
                    $achievementResult->tier->value,
                );
            }
        }

        // Season reward events
        foreach ($result->seasonResults as $seasonResult) {
            if ($seasonResult->coinsGranted > 0 || $seasonResult->xpGranted > 0) {
                SeasonRewardGranted::dispatch(
                    $seasonResult->userId,
                    $seasonResult->seasonId,
                    'default',
                );
            }
        }
    }
}
