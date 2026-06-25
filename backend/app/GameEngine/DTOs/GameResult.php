<?php
declare(strict_types=1);

namespace App\GameEngine\DTOs;

use App\GameEngine\Events\GameEvent;

/**
 * Complete, immutable record of all state changes that resulted from
 * processing a single GameEvent through the full pipeline.
 *
 * Returned by GameEngineContract::process(). The HTTP layer serializes
 * this into a JSON response so the client can animate XP bars, level-up
 * overlays, mission completions, etc. in a single round-trip.
 */
final readonly class GameResult
{
    /**
     * @param  XPResult[]          $xpResults
     * @param  LevelResult[]       $levelResults
     * @param  CareerResult[]      $careerResults
     * @param  MissionResult[]     $missionResults
     * @param  AchievementResult[] $achievementResults
     * @param  LeagueResult[]      $leagueResults
     * @param  SeasonResult[]      $seasonResults
     * @param  RewardResult[]      $rewardResults
     */
    public function __construct(
        public readonly GameEvent $triggerEvent,
        public readonly int       $userId,
        public readonly array     $xpResults,
        public readonly array     $levelResults,
        public readonly array     $careerResults,
        public readonly array     $missionResults,
        public readonly array     $achievementResults,
        public readonly array     $leagueResults,
        public readonly array     $seasonResults,
        public readonly array     $rewardResults,
        public readonly float     $processingTimeMs,
    ) {}

    public function totalXPGranted(): int
    {
        return array_sum(array_column($this->xpResults, 'amountGranted'));
    }

    public function totalCoinsGranted(): int
    {
        return array_sum(array_column($this->rewardResults, 'coinsGranted'));
    }

    public function didLevelUp(): bool
    {
        foreach ($this->levelResults as $lr) {
            if ($lr->levelsGained() > 0) {
                return true;
            }
        }
        return false;
    }

    public function missionsCompleted(): int
    {
        return count(array_filter($this->missionResults, fn ($m) => $m->justCompleted));
    }

    public function achievementsUnlocked(): int
    {
        return count(array_filter($this->achievementResults, fn ($a) => $a->justUnlocked));
    }

    /**
     * True if anything noteworthy happened (XP gained, level up, etc.)
     * Used by the HTTP layer to decide whether to send a full response body.
     */
    public function hasSignificantChanges(): bool
    {
        return $this->totalXPGranted() > 0
            || $this->totalCoinsGranted() > 0
            || $this->didLevelUp()
            || $this->missionsCompleted() > 0
            || $this->achievementsUnlocked() > 0;
    }
}
