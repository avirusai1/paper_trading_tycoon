<?php

declare(strict_types=1);

namespace App\Trading\Validators;

use App\GameEngine\Contracts\GameRuleProviderContract;
use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\TradingValidatorContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\TradingValidationReason;
use App\Trading\Exceptions\TradingValidationException;
use Carbon\Carbon;

/**
 * Validates that the trade is placed during official market trading hours (Mon-Fri, 9:15 AM - 3:30 PM IST).
 */
final readonly class TradingHoursValidator implements TradingValidatorContract
{
    public function __construct(
        private GameRuleProviderContract $ruleProvider
    ) {}

    public function validate(TradeRequest $request, TradingContext $context): void
    {
        $nowIST = Carbon::instance($context->builtAt)->setTimezone('Asia/Kolkata');

        // Check for weekends
        if ($nowIST->isWeekend()) {
            throw new TradingValidationException(
                TradingValidationReason::OutsideTradingHours,
                'Trading is not allowed on weekends.'
            );
        }

        // Fetch market hours from rules
        $openTimeStr = $this->ruleProvider->getString('market.open_time_ist', '09:15');
        $closeTimeStr = $this->ruleProvider->getString('market.close_time_ist', '15:30');

        [$openHour, $openMin] = explode(':', $openTimeStr);
        [$closeHour, $closeMin] = explode(':', $closeTimeStr);

        $openTime = $nowIST->copy()->setTime((int) $openHour, (int) $openMin, 0);
        $closeTime = $nowIST->copy()->setTime((int) $closeHour, (int) $closeMin, 0);

        if ($nowIST->lt($openTime) || $nowIST->gt($closeTime)) {
            throw new TradingValidationException(
                TradingValidationReason::OutsideTradingHours,
                "Trading is only allowed between {$openTimeStr} and {$closeTimeStr} IST."
            );
        }
    }
}
