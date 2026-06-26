<?php

declare(strict_types=1);

namespace App\GameEngine\Processors;

use App\GameEngine\Actions\GrantLeagueProgressAction;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\Contracts\LeagueProcessorContract;
use App\GameEngine\DTOs\LeagueResult;

/**
 * Implements LeagueProcessorContract by delegating to GrantLeagueProgressAction.
 */
final class LeagueProcessor implements LeagueProcessorContract
{
    public function __construct(
        private readonly GrantLeagueProgressAction $leagueAction,
    ) {}

    public function updateSeasonStanding(GameContext $context, int $portfolioValuePaise): LeagueResult
    {
        return $this->leagueAction->updateSeasonStanding($context, $portfolioValuePaise);
    }

    public function processSeasonEnd(GameContext $context, int $seasonId): LeagueResult
    {
        return $this->leagueAction->processSeasonEnd($context, $seasonId);
    }
}
