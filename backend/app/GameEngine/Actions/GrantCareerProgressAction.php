<?php

declare(strict_types=1);

namespace App\GameEngine\Actions;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\CareerResult;
use App\GameEngine\Exceptions\CareerException;
use App\Models\CareerTitle;
use App\Models\UserLevel;

/**
 * Evaluates and updates the user's career title based on their current level.
 *
 * Career titles are defined in the career_titles table, each covering a range
 * of levels. This action checks whether the user's current level places them
 * in a different title than what's recorded in user_levels.career_title, and
 * persists the update if so.
 *
 * CareerTitle::forLevel() uses DB-defined ranges — no hardcoded thresholds.
 */
final class GrantCareerProgressAction
{
    /**
     * @throws CareerException
     */
    public function execute(GameContext $context): CareerResult
    {
        $currentLevel = $context->currentLevel();
        $titleBefore = $context->userLevel->career_title;

        $careerTitle = CareerTitle::forLevel($currentLevel);

        if ($careerTitle === null) {
            throw CareerException::noTitleForLevel($currentLevel);
        }

        $titleAfter = $careerTitle->title;
        $titleChanged = $titleAfter !== $titleBefore;

        if ($titleChanged) {
            UserLevel::where('user_id', $context->userId())
                ->update(['career_title' => $titleAfter]);
        }

        return new CareerResult(
            userId: $context->userId(),
            currentLevel: $currentLevel,
            titleBefore: $titleBefore,
            titleAfter: $titleAfter,
            titleChanged: $titleChanged,
        );
    }
}
