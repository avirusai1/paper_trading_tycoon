<?php

declare(strict_types=1);

namespace App\GameEngine\Actions;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\AchievementResult;
use App\GameEngine\Events\GameEvent;
use App\GameEngine\Support\AchievementCriteriaEvaluator;
use App\Models\Achievement;
use App\Models\UserAchievement;
use Illuminate\Support\Facades\DB;

/**
 * Evaluates all active achievements and unlocks any that are newly satisfied.
 *
 * Handles both one-time and repeatable achievements. For non-repeatable
 * achievements the context's unlockedAchievementIds set is consulted first
 * to skip re-evaluation — avoiding a DB lock on every event.
 */
final class GrantAchievementProgressAction
{
    public function __construct(
        private readonly AchievementCriteriaEvaluator $criteriaEvaluator,
    ) {}

    /**
     * @return AchievementResult[]
     */
    public function execute(GameContext $context, GameEvent $event): array
    {
        $achievements = Achievement::where('is_active', true)->get();
        $results = [];

        foreach ($achievements as $achievement) {
            // Skip non-repeatable achievements already unlocked
            if (! $achievement->is_repeatable
                && $context->hasUnlockedAchievement($achievement->id)) {
                continue;
            }

            if (! $this->criteriaEvaluator->isSatisfied($achievement, $context, $event)) {
                continue;
            }

            $result = DB::transaction(function () use ($context, $achievement): AchievementResult {
                $existing = UserAchievement::where('user_id', $context->userId())
                    ->where('achievement_id', $achievement->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing !== null && ! $achievement->is_repeatable) {
                    // Beaten to the lock — already unlocked
                    return new AchievementResult(
                        userId: $context->userId(),
                        achievementId: $achievement->id,
                        achievementKey: $achievement->key,
                        achievementName: $achievement->name,
                        tier: $achievement->tier,
                        unlockCount: $existing->unlock_count,
                        justUnlocked: false,
                        xpReward: 0,
                        coinReward: 0,
                    );
                }

                if ($existing !== null) {
                    // Repeatable — increment count
                    $existing->increment('unlock_count');
                    $existing->update(['last_unlocked_at' => now()]);
                    $unlockCount = $existing->unlock_count;
                } else {
                    UserAchievement::create([
                        'user_id' => $context->userId(),
                        'achievement_id' => $achievement->id,
                        'unlock_count' => 1,
                        'first_unlocked_at' => now(),
                        'last_unlocked_at' => now(),
                    ]);
                    $unlockCount = 1;
                }

                return new AchievementResult(
                    userId: $context->userId(),
                    achievementId: $achievement->id,
                    achievementKey: $achievement->key,
                    achievementName: $achievement->name,
                    tier: $achievement->tier,
                    unlockCount: $unlockCount,
                    justUnlocked: true,
                    xpReward: $achievement->xp_reward,
                    coinReward: $achievement->coin_reward,
                );
            });

            $results[] = $result;
        }

        return $results;
    }
}
