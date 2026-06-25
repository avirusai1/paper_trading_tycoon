<?php
declare(strict_types=1);

namespace App\GameEngine\Contracts;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\AchievementResult;
use App\GameEngine\Events\GameEvent;

/**
 * Contract for the Achievement processing subsystem.
 *
 * Evaluates whether any achievement definitions are newly satisfied by the
 * current game state after processing an event. Handles both progress-based
 * and threshold-based achievements.
 */
interface AchievementProcessorContract
{
    /**
     * Evaluate all active achievements against the post-event context.
     * Unlocks any newly satisfied achievements and grants their rewards.
     *
     * @return AchievementResult[]
     * @throws \App\GameEngine\Exceptions\AchievementException
     */
    public function evaluate(GameContext $context, GameEvent $event): array;
}
