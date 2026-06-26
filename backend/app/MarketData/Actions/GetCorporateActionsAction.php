<?php

declare(strict_types=1);

namespace App\MarketData\Actions;

use App\MarketData\Contracts\CorporateActionProviderContract;
use App\MarketData\DTOs\CorporateAction;
use App\MarketData\Support\ProviderCoordinator;
use App\MarketData\Validators\TickerValidator;
use App\MarketData\ValueObjects\Ticker;

final class GetCorporateActionsAction
{
    public function __construct(private ProviderCoordinator $coordinator) {}

    /**
     * @return CorporateAction[]
     */
    public function execute(Ticker $ticker): array
    {
        TickerValidator::validate($ticker->symbol);

        return $this->coordinator->execute(
            CorporateActionProviderContract::class,
            fn (CorporateActionProviderContract $provider) => $provider->getCorporateActions($ticker)
        );
    }
}
