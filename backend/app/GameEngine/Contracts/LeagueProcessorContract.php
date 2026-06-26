<?php

declare(strict_types=1);

namespace App\GameEngine\Contracts;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\LeagueResult;
use App\GameEngine\Exceptions\LeagueException;
use App\GameEngine\Exceptions\SeasonException;

/**
 * Contract for the League progression subsystem.
 *
 * Handles mid-season portfolio value updates to league records and
 * end-of-season promotion/demotion calculation.
 */
interface LeagueProcessorContract
{
    /**
     * Update the user's in-season portfolio value on the user_leagues record.
     * Called after every portfolio snapshot.
     *
     * @throws LeagueException
     */
    public function updateSeasonStanding(
        GameContext $context,
        int $portfolioValuePaise,
    ): LeagueResult;

    /**
     * Process end-of-season league result for a user: determine promotion/
     * demotion, update user_leagues.season_result, and trigger season rewards.
     *
     * @throws SeasonException
     * @throws LeagueException
     */
    public function processSeasonEnd(GameContext $context, int $seasonId): LeagueResult;
}
