<?php
declare(strict_types=1);

namespace App\GameEngine\Processors;

use App\Enums\CoinTransactionSource;
use App\GameEngine\Actions\GrantCoinsAction;
use App\GameEngine\Contracts\GameRuleProviderContract;
use App\GameEngine\Contracts\RewardProcessorContract;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\RewardResult;
use App\GameEngine\Exceptions\RewardException;

/**
 * Implements RewardProcessorContract.
 *
 * Resolves coin amounts from the Rules Engine when coinAmount is 0,
 * then delegates to GrantCoinsAction for persistence.
 */
final class RewardProcessor implements RewardProcessorContract
{
    public function __construct(
        private readonly GrantCoinsAction        $grantCoins,
        private readonly GameRuleProviderContract $rules,
    ) {}

    public function grantCoins(
        GameContext           $context,
        CoinTransactionSource $source,
        string                $sourceId,
        int                   $coinAmount = 0,
    ): RewardResult {
        $amount = $coinAmount > 0
            ? $coinAmount
            : $this->resolveAmountFromRules($source, $context);

        if ($amount <= 0) {
            // No coins configured for this source — no-op
            return new RewardResult(
                userId:        $context->userId(),
                coinsGranted:  0,
                balanceBefore: $context->coinBalance(),
                balanceAfter:  $context->coinBalance(),
                source:        $source->value,
                sourceId:      $sourceId,
            );
        }

        // Apply coin multiplier (from premium / equipped items)
        $multiplied = (int) floor($amount * $context->coinMultiplier());

        return $this->grantCoins->credit($context, $source, $sourceId, $multiplied);
    }

    private function resolveAmountFromRules(CoinTransactionSource $source, GameContext $context): int
    {
        $key = match ($source) {
            CoinTransactionSource::DailyLogin   => 'coins.daily_login',
            CoinTransactionSource::LevelUp      => 'coins.level_up',
            CoinTransactionSource::Referral      => 'coins.referral',
            CoinTransactionSource::SeasonReward => 'coins.season_reward_base',
            default                              => null,
        };

        if ($key === null) {
            return 0;
        }

        return $this->rules->getInt($key, 0);
    }
}
