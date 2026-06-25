<?php
declare(strict_types=1);

namespace App\GameEngine\States;

use App\GameEngine\DTOs\AchievementResult;
use App\GameEngine\DTOs\CareerResult;
use App\GameEngine\DTOs\GameResult;
use App\GameEngine\DTOs\LeagueResult;
use App\GameEngine\DTOs\LevelResult;
use App\GameEngine\DTOs\MissionResult;
use App\GameEngine\DTOs\RewardResult;
use App\GameEngine\DTOs\SeasonResult;
use App\GameEngine\DTOs\XPResult;
use App\GameEngine\Events\GameEvent;

/**
 * Mutable builder for GameResult — used internally by the pipeline to
 * accumulate results across stages before creating the final immutable DTO.
 *
 * This class is intentionally internal to the pipeline and not part of any
 * public contract. Callers outside the pipeline always receive GameResult.
 *
 * @internal
 */
final class GameResultBuilder
{
    /** @var XPResult[] */
    private array $xpResults = [];

    /** @var LevelResult[] */
    private array $levelResults = [];

    /** @var CareerResult[] */
    private array $careerResults = [];

    /** @var MissionResult[] */
    private array $missionResults = [];

    /** @var AchievementResult[] */
    private array $achievementResults = [];

    /** @var LeagueResult[] */
    private array $leagueResults = [];

    /** @var SeasonResult[] */
    private array $seasonResults = [];

    /** @var RewardResult[] */
    private array $rewardResults = [];

    private readonly float $startedAt;

    public function __construct(
        private readonly GameEvent $event,
        private readonly int       $userId,
    ) {
        $this->startedAt = microtime(true);
    }

    public function addXP(XPResult $result): self               { $this->xpResults[]          = $result; return $this; }
    public function addLevel(LevelResult $result): self         { $this->levelResults[]        = $result; return $this; }
    public function addCareer(CareerResult $result): self       { $this->careerResults[]       = $result; return $this; }
    public function addMission(MissionResult $result): self     { $this->missionResults[]      = $result; return $this; }
    public function addAchievement(AchievementResult $r): self  { $this->achievementResults[]  = $r;      return $this; }
    public function addLeague(LeagueResult $result): self       { $this->leagueResults[]       = $result; return $this; }
    public function addSeason(SeasonResult $result): self       { $this->seasonResults[]       = $result; return $this; }
    public function addReward(RewardResult $result): self       { $this->rewardResults[]       = $result; return $this; }

    /** @param MissionResult[] $results */
    public function addMissions(array $results): self
    {
        foreach ($results as $r) {
            $this->missionResults[] = $r;
        }
        return $this;
    }

    /** @param AchievementResult[] $results */
    public function addAchievements(array $results): self
    {
        foreach ($results as $r) {
            $this->achievementResults[] = $r;
        }
        return $this;
    }

    public function build(): GameResult
    {
        return new GameResult(
            triggerEvent:       $this->event,
            userId:             $this->userId,
            xpResults:          $this->xpResults,
            levelResults:       $this->levelResults,
            careerResults:      $this->careerResults,
            missionResults:     $this->missionResults,
            achievementResults: $this->achievementResults,
            leagueResults:      $this->leagueResults,
            seasonResults:      $this->seasonResults,
            rewardResults:      $this->rewardResults,
            processingTimeMs:   round((microtime(true) - $this->startedAt) * 1000, 2),
        );
    }
}
