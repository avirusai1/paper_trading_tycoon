<?php
declare(strict_types=1);

namespace App\GameEngine\Actions;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\LeagueResult;
use App\GameEngine\Exceptions\LeagueException;
use App\Enums\LeagueTier;
use App\Models\League;
use App\Models\Season;
use App\Models\UserLeague;
use Illuminate\Support\Facades\DB;

/**
 * Updates the user's league standing within the active season.
 *
 * Two operations:
 * 1. updateSeasonStanding() — called on every portfolio snapshot.
 * 2. processSeasonEnd()     — called at season close, determines promotion/demotion.
 */
final class GrantLeagueProgressAction
{
    /**
     * @throws LeagueException
     */
    public function updateSeasonStanding(GameContext $context, int $portfolioValuePaise): LeagueResult
    {
        if ($context->activeSeason === null || $context->currentLeague === null) {
            throw LeagueException::noLeagueForUser($context->userId(), 0);
        }

        $userLeague = $context->currentLeague;

        // Compute return percent from starting virtual cash
        $startingCash   = $context->wallet->total_deposited_paise;
        $returnPercent  = $startingCash > 0
            ? round(($portfolioValuePaise - $startingCash) / $startingCash * 100, 2)
            : 0.0;

        $userLeague->update([
            'season_portfolio_value_paise' => $portfolioValuePaise,
            'season_return_percent'        => $returnPercent,
        ]);

        return new LeagueResult(
            userId:              $context->userId(),
            seasonId:            $context->activeSeason->id,
            tier:                LeagueTier::from($userLeague->tier),
            rankPosition:        $userLeague->rank_position,
            portfolioValuePaise: $portfolioValuePaise,
            returnPercent:       $returnPercent,
        );
    }

    /**
     * @throws LeagueException
     */
    public function processSeasonEnd(GameContext $context, int $seasonId): LeagueResult
    {
        $season = Season::findOrFail($seasonId);

        $userLeague = UserLeague::where('user_id', $context->userId())
            ->where('season_id', $seasonId)
            ->lockForUpdate()
            ->first();

        if ($userLeague === null) {
            throw LeagueException::noLeagueForUser($context->userId(), $seasonId);
        }

        $league        = League::where('tier', $userLeague->tier)->firstOrFail();
        $totalInTier   = UserLeague::where('season_id', $seasonId)->where('tier', $userLeague->tier)->count();
        $rank          = $userLeague->rank_position;

        $promoteThreshold = (int) ceil($totalInTier * ($league->promote_top_percent / 100));
        $demoteThreshold  = (int) floor($totalInTier * (1 - $league->demote_bottom_percent / 100));

        $currentTier = LeagueTier::from($userLeague->tier);
        $seasonResult = 'stayed';
        $newTier      = $currentTier;

        if ($rank <= $promoteThreshold && $currentTier->next() !== null) {
            $seasonResult = 'promoted';
            $newTier      = $currentTier->next();
        } elseif ($rank > $demoteThreshold && $currentTier->previous() !== null) {
            $seasonResult = 'demoted';
            $newTier      = $currentTier->previous();
        }

        $userLeague->update(['season_result' => $seasonResult]);

        return new LeagueResult(
            userId:              $context->userId(),
            seasonId:            $seasonId,
            tier:                $newTier,
            rankPosition:        $rank,
            portfolioValuePaise: $userLeague->season_portfolio_value_paise,
            returnPercent:       $userLeague->season_return_percent,
            seasonResult:        $seasonResult,
        );
    }

    /**
     * Enrol a user into the currently active season if not already enrolled.
     * Idempotent — safe to call on every login.
     */
    public function ensureEnrolled(GameContext $context): void
    {
        if ($context->activeSeason === null) {
            return;
        }

        $seasonId = $context->activeSeason->id;

        // Find the lowest league tier (Bronze)
        $bronzeLeague = League::where('rank', 1)->first();
        if ($bronzeLeague === null) {
            return;
        }

        DB::transaction(function () use ($context, $seasonId, $bronzeLeague): void {
            UserLeague::firstOrCreate(
                ['user_id' => $context->userId(), 'season_id' => $seasonId],
                [
                    'league_id'                    => $bronzeLeague->id,
                    'tier'                         => $bronzeLeague->tier->value,
                    'rank_position'                => 0,
                    'season_portfolio_value_paise' => $context->wallet->virtual_cash_paise,
                    'season_return_percent'        => 0.0,
                    'season_result'                => null,
                    'rewards_claimed'              => false,
                ],
            );
        });
    }
}
