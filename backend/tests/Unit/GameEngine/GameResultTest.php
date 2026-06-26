<?php

declare(strict_types=1);

namespace Tests\Unit\GameEngine;

use App\Enums\AchievementTier;
use App\GameEngine\DTOs\AchievementResult;
use App\GameEngine\DTOs\GameResult;
use App\GameEngine\DTOs\LevelResult;
use App\GameEngine\DTOs\MissionResult;
use App\GameEngine\DTOs\RewardResult;
use App\GameEngine\DTOs\XPResult;
use App\GameEngine\Events\DailyLoginEvent;
use Tests\TestCase;

/**
 * Unit tests for GameResult.
 *
 * Coverage targets:
 * - totalXPGranted() sums amountGranted across all XPResult instances
 * - totalCoinsGranted() sums coinsGranted across all RewardResult instances
 * - didLevelUp() is true when any LevelResult has levelsGained > 0
 * - missionsCompleted() counts justCompleted = true mission results
 * - achievementsUnlocked() counts justUnlocked = true achievement results
 * - hasSignificantChanges() is false for a completely empty result
 * - hasSignificantChanges() is true when any metric > 0
 */
final class GameResultTest extends TestCase
{
    private DailyLoginEvent $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->event = new DailyLoginEvent(1, '2025-01-01', 3);
    }

    /** @test */
    public function total_xp_granted_sums_all_xp_results(): void
    {
        $result = $this->makeResult(xpAmounts: [10, 75, 100]);

        $this->assertSame(185, $result->totalXPGranted());
    }

    /** @test */
    public function total_coins_granted_sums_all_reward_results(): void
    {
        $result = $this->makeResult(coinAmounts: [100, 250]);

        $this->assertSame(350, $result->totalCoinsGranted());
    }

    /** @test */
    public function did_level_up_is_true_when_level_result_has_gain(): void
    {
        $levelResult = $this->makeLevelResult(levelBefore: 3, levelAfter: 4);
        $result = $this->makeResult(levelResults: [$levelResult]);

        $this->assertTrue($result->didLevelUp());
    }

    /** @test */
    public function did_level_up_is_false_when_no_level_results(): void
    {
        $result = $this->makeResult();

        $this->assertFalse($result->didLevelUp());
    }

    /** @test */
    public function missions_completed_counts_just_completed(): void
    {
        $m1 = $this->makeMissionResult(justCompleted: true);
        $m2 = $this->makeMissionResult(justCompleted: false);
        $m3 = $this->makeMissionResult(justCompleted: true);

        $result = $this->makeResult(missionResults: [$m1, $m2, $m3]);

        $this->assertSame(2, $result->missionsCompleted());
    }

    /** @test */
    public function achievements_unlocked_counts_just_unlocked(): void
    {
        $a1 = $this->makeAchievementResult(justUnlocked: true);
        $a2 = $this->makeAchievementResult(justUnlocked: false);

        $result = $this->makeResult(achievementResults: [$a1, $a2]);

        $this->assertSame(1, $result->achievementsUnlocked());
    }

    /** @test */
    public function has_no_significant_changes_for_empty_result(): void
    {
        $result = $this->makeResult();

        $this->assertFalse($result->hasSignificantChanges());
    }

    /** @test */
    public function has_significant_changes_when_xp_granted(): void
    {
        $result = $this->makeResult(xpAmounts: [10]);

        $this->assertTrue($result->hasSignificantChanges());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeResult(
        array $xpAmounts = [],
        array $coinAmounts = [],
        array $levelResults = [],
        array $missionResults = [],
        array $achievementResults = [],
    ): GameResult {
        $xpResults = array_map(fn ($amt) => new XPResult(1, $amt, 0, $amt, 1, 1, false, 'daily_login', 'src', false), $xpAmounts);
        $rewardResults = array_map(fn ($amt) => new RewardResult(1, $amt, 0, $amt, 'level_up', 'src'), $coinAmounts);

        return new GameResult(
            triggerEvent: $this->event,
            userId: 1,
            xpResults: $xpResults,
            levelResults: $levelResults,
            careerResults: [],
            missionResults: $missionResults,
            achievementResults: $achievementResults,
            leagueResults: [],
            seasonResults: [],
            rewardResults: $rewardResults,
            processingTimeMs: 1.5,
        );
    }

    private function makeLevelResult(int $levelBefore, int $levelAfter): LevelResult
    {
        return new LevelResult(1, $levelBefore, $levelAfter, 'Student Trader', 'Intern Trader', 200, []);
    }

    private function makeMissionResult(bool $justCompleted): MissionResult
    {
        return new MissionResult(1, 1, 1, 'test_mission', 0, 5, 5, $justCompleted, false, 50, 100);
    }

    private function makeAchievementResult(bool $justUnlocked): AchievementResult
    {
        return new AchievementResult(1, 1, 'first_trade', 'First Trade', AchievementTier::Bronze, 1, $justUnlocked, 75, 100);
    }
}
