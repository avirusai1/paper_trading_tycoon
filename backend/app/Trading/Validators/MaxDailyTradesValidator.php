<?php

declare(strict_types=1);

namespace App\Trading\Validators;

use App\GameEngine\Contracts\GameRuleProviderContract;
use App\Models\Trade;
use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\TradingValidatorContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\TradingValidationReason;
use App\Trading\Exceptions\TradingValidationException;
use Carbon\Carbon;

/**
 * Validates that the user has not exceeded the maximum allowed trades per minute or per day.
 */
final readonly class MaxDailyTradesValidator implements TradingValidatorContract
{
    public function __construct(
        private GameRuleProviderContract $ruleProvider
    ) {}

    public function validate(TradeRequest $request, TradingContext $context): void
    {
        $userId = $context->userId();
        $now = Carbon::instance($context->builtAt);

        // 1. Minute Limit Check (Velocity)
        $maxPerMinute = $this->ruleProvider->getInt('anticheat.max_trades_per_minute', 10);
        $oneMinuteAgo = $now->copy()->subMinute();
        $tradesLastMinute = Trade::query()
            ->where('user_id', $userId)
            ->where('executed_at', '>=', $oneMinuteAgo)
            ->count();

        if ($tradesLastMinute >= $maxPerMinute) {
            throw new TradingValidationException(
                TradingValidationReason::MaxDailyTradesExceeded,
                "Velocity limit hit. You cannot place more than {$maxPerMinute} trades per minute."
            );
        }

        // 2. Daily Limit Check
        $maxPerDay = $this->ruleProvider->getInt('anticheat.max_trades_per_day', 100);
        $startOfDay = $now->copy()->startOfDay();
        $tradesToday = Trade::query()
            ->where('user_id', $userId)
            ->where('executed_at', '>=', $startOfDay)
            ->count();

        if ($tradesToday >= $maxPerDay) {
            throw new TradingValidationException(
                TradingValidationReason::MaxDailyTradesExceeded,
                "Daily limit hit. You have reached your maximum limit of {$maxPerDay} trades per day."
            );
        }
    }
}
