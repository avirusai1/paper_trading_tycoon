<?php

declare(strict_types=1);

namespace App\Trading\Services;

use App\Trading\Contracts\TradingEngineContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\DTOs\TradeResult;
use App\Trading\Pipelines\TradingPipeline;

/**
 * Concrete implementation of the TradingEngineContract facade.
 */
final readonly class TradingEngine implements TradingEngineContract
{
    public function __construct(
        private TradingPipeline $pipeline
    ) {}

    public function execute(TradeRequest $request): TradeResult
    {
        return $this->pipeline->execute($request);
    }
}
