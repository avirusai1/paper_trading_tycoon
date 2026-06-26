<?php

declare(strict_types=1);

namespace App\GameEngine\Processors;

use App\GameEngine\Actions\GrantLeagueProgressAction;
use App\GameEngine\Actions\GrantSeasonProgressAction;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\Contracts\SeasonProcessorContract;
use App\GameEngine\DTOs\SeasonResult;
use App\GameEngine\Exceptions\SeasonException;
use App\Models\Season;

/**
 * Implements SeasonProcessorContract.
 */
final class SeasonProcessor implements SeasonProcessorContract
{
    public function __construct(
        private readonly GrantLeagueProgressAction $leagueAction,
        private readonly GrantSeasonProgressAction $seasonAction,
    ) {}

    public function ensureEnrolled(GameContext $context): SeasonResult
    {
        if ($context->activeSeason === null) {
            throw SeasonException::noActiveSeason();
        }

        $this->leagueAction->ensureEnrolled($context);

        return new SeasonResult(
            userId: $context->userId(),
            seasonId: $context->activeSeason->id,
            seasonName: $context->activeSeason->name,
            enrolled: true,
            coinsGranted: 0,
            xpGranted: 0,
        );
    }

    public function distributeRewards(GameContext $context, Season $season): SeasonResult
    {
        return $this->seasonAction->distributeRewards($context, $season);
    }
}
