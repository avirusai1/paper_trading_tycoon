<?php

declare(strict_types=1);

namespace App\RewardEngine\Calculators;

use App\GameEngine\Contracts\GameRuleProviderContract;
use App\Models\SeasonReward;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\RewardType;
use App\RewardEngine\Exceptions\RewardCalculationException;

/**
 * Calculates the season-end reward payout for a user's final league rank.
 *
 * Looks up the SeasonReward row matching (season_id, league_id, rank_from, rank_to)
 * for the user's final rank in the season.
 *
 * Required metadata keys:
 *   - 'season_id'    (int)  The closing season.
 *   - 'final_rank'   (int)  The user's final rank in the league.
 *   - 'league_id'    (int)  The user's league for this season.
 */
final class SeasonBonusCalculator
{
    public function __construct(
        private readonly GameRuleProviderContract $rules,
    ) {}

    /**
     * @throws RewardCalculationException
     */
    public function calculate(RewardRequest $request, RewardContext $context): CalculatedReward
    {
        $seasonId = (int) $request->meta('season_id', 0);
        $finalRank = (int) $request->meta('final_rank', 0);
        $leagueId = (int) $request->meta('league_id', 0);

        if ($seasonId === 0 || $finalRank === 0 || $leagueId === 0) {
            throw RewardCalculationException::missingRule('season_bonus requires season_id, final_rank, league_id in metadata');
        }

        /** @var SeasonReward|null $seasonReward */
        $seasonReward = SeasonReward::query()
            ->where('season_id', $seasonId)
            ->where('league_id', $leagueId)
            ->where('rank_from', '<=', $finalRank)
            ->where('rank_to', '>=', $finalRank)
            ->first();

        if ($seasonReward === null) {
            // No reward configured for this rank band — zero reward (not an error)
            return new CalculatedReward(
                rewardType: RewardType::SeasonReward,
                idempotencyKey: $request->idempotencyKey,
                userId: $request->userId,
                isDryRun: $request->dryRun,
            );
        }

        return new CalculatedReward(
            rewardType: RewardType::SeasonReward,
            idempotencyKey: $request->idempotencyKey,
            userId: $request->userId,
            baseXP: (int) $seasonReward->xp_reward,
            finalXP: (int) $seasonReward->xp_reward,
            baseCoins: (int) $seasonReward->coin_reward,
            finalCoins: (int) $seasonReward->coin_reward,
            extras: [
                'title_reward' => $seasonReward->title_reward,
                'extra_rewards' => $seasonReward->extra_rewards ?? [],
                'season_id' => $seasonId,
                'league_id' => $leagueId,
                'rank' => $finalRank,
            ],
            isDryRun: $request->dryRun,
        );
    }
}
