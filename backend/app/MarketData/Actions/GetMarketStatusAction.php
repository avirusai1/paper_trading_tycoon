<?php

declare(strict_types=1);

namespace App\MarketData\Actions;

use App\MarketData\Contracts\MarketStatusProviderContract;
use App\MarketData\DTOs\MarketStatus;
use App\MarketData\Support\ProviderCoordinator;
use App\MarketData\Validators\ExchangeValidator;
use App\MarketData\ValueObjects\Exchange;

final class GetMarketStatusAction
{
    public function __construct(private ProviderCoordinator $coordinator) {}

    public function execute(Exchange $exchange): MarketStatus
    {
        ExchangeValidator::validate($exchange->value);

        return $this->coordinator->execute(
            MarketStatusProviderContract::class,
            fn (MarketStatusProviderContract $provider) => $provider->getMarketStatus($exchange)
        );
    }
}
