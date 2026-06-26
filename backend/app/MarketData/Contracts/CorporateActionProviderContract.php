<?php

declare(strict_types=1);

namespace App\MarketData\Contracts;

use App\MarketData\DTOs\CorporateAction;
use App\MarketData\ValueObjects\Ticker;

interface CorporateActionProviderContract extends MarketDataProviderContract
{
    /**
     * @return CorporateAction[]
     */
    public function getCorporateActions(Ticker $ticker): array;
}
