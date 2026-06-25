<?php
declare(strict_types=1);

namespace App\GameEngine\Actions;

use App\Enums\CoinTransactionSource;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\SeasonResult;
use App\GameEngine\Exceptions\SeasonException;
use App\Models\Season;
use App\Models\SeasonReward;
use App\Models\UserLeague;
use Illuminate\Support\Facades\DB;

/**
 * Handles end-of-season reward distribution.
 *
 * Queries the season_rewards table to find the reward tier matching the user's
 * final league standing. Delegates coin and XP grants to the respective action
 * classes (injected). Marks rewards_claimed on user_leagues to prevent
 * double-distribution.
 */
final class GrantSeasonProgressAction
{
    public function __construct(
        private readonly GrantCoinsAction $grantCoins,
        private readonly GrantXPAction    $grantXP,
    ) {}

    /**
     * @throws SeasonException
     */
    public function distributeRewards(GameContext $context, Season $season): SeasonResult
    {
        if ($season->status !== 'ended') {
            throw SeasonException::seasonNotEnded($season->id);
        }

        return DB::transaction(function () use ($context, $season): SeasonResult {
            $userLeague = UserLeague::where('user_id', $context->userId())
                ->where('season_id', $season->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($userLeague->rewards_claimed) {
                return new SeasonResult(
                    userId:       $context->userId(),
                    seasonId:     $season->id,
                    seasonName:   $season->name,
                    enrolled:     false,
                    coinsGranted: 0,
                    xpGranted:    0,
                );
            }

            // Find reward record for this league + rank
            $reward = SeasonReward::where('league_id', $userLeague->league_id)
                ->where('season_id', $season->id)
                ->where('rank_min', '<=', $userLeague->rank_position)
                ->where('rank_max', '>=', $userLeague->rank_position)
                ->first();

            $coinsGranted = 0;
            $xpGranted    = 0;

            if ($reward !== null) {
                $sourceId = "season_{$season->id}_user_{$context->userId()}";

                if ($reward->coin_reward > 0) {
                    $rewardResult  = $this->grantCoins->credit(
                        $context,
                        CoinTransactionSource::SeasonReward,
                        $sourceId,
                        $reward->coin_reward,
                        "Season {$season->name} reward",
                    );
                    $coinsGranted = $rewardResult->coinsGranted;
                }

                if ($reward->xp_reward > 0) {
                    $xpResult  = $this->grantXP->execute(
                        $context,
                        \App\GameEngine\Enums\XPSource::SeasonReward,
                        $sourceId,
                        $reward->xp_reward,
                    );
                    $xpGranted = $xpResult->amountGranted;
                }
            }

            $userLeague->update(['rewards_claimed' => true]);

            return new SeasonResult(
                userId:       $context->userId(),
                seasonId:     $season->id,
                seasonName:   $season->name,
                enrolled:     false,
                coinsGranted: $coinsGranted,
                xpGranted:    $xpGranted,
            );
        });
    }
}
