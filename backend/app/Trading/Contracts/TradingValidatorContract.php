<?php

declare(strict_types=1);

namespace App\Trading\Contracts;

use App\Trading\Contexts\TradingContext;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Exceptions\TradingValidationException;

/**
 * Paper Trading Tycoon — Trading Validator Contract
 */
interface TradingValidatorContract
{
    /**
     * Validate the given request against the hydrated context.
     *
     * @throws TradingValidationException
     */
    public function validate(TradeRequest $request, TradingContext $context): void;
}
