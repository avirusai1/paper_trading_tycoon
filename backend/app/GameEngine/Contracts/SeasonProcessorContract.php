<?php

declare(strict_types=1);

namespace App\GameEngine\Contracts;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\SeasonResult;
use App\GameEngine\Exceptions\SeasonException;
use App\Models\Season;

/**
 * Contract for the Season lifecycle subsystem.
 *
 * Manages enrolment of new users into the current season and
 * the end-of-season reward distribution flow.
 */
interface SeasonProcessorContract
{
    /**
     * Ensure the user is enrolled in the currently active season.
     * Idempotent — safe to call on every login.
     *
     * @throws SeasonException
     */
    public function ensureEnrolled(GameContext $context): SeasonResult;

    /**
     * Distribute end-of-season rewards for a user based on their final
     * league rank within the completed season.
     *
     * @throws SeasonException
     */
    public function distributeRewards(GameContext $context, Season $season): SeasonResult;
}
