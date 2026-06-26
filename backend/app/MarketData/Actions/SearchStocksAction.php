<?php

declare(strict_types=1);

namespace App\MarketData\Actions;

use App\MarketData\Contracts\SearchProviderContract;
use App\MarketData\Contracts\StockRepositoryContract;
use App\MarketData\DTOs\SearchResult;
use App\MarketData\Support\ProviderCoordinator;
use App\Models\Stock;

final class SearchStocksAction
{
    public function __construct(
        private ProviderCoordinator $coordinator,
        private StockRepositoryContract $repository
    ) {}

    /**
     * @return SearchResult[]
     */
    public function execute(string $query): array
    {
        $dbResults = $this->repository->searchStocks($query);

        if (count($dbResults) >= 3) {
            return $dbResults;
        }

        try {
            $providerResults = $this->coordinator->execute(
                SearchProviderContract::class,
                fn (SearchProviderContract $provider) => $provider->searchStocks($query)
            );

            foreach ($providerResults as $result) {
                $stock = $this->repository->findStockByTicker($result->ticker);
                if (! $stock) {
                    Stock::create([
                        'symbol' => $result->ticker->symbol,
                        'name' => $result->name->value,
                        'exchange' => $result->exchange->value,
                        'isin' => $result->isin,
                        'sector' => $result->sector?->value,
                        'industry' => $result->industry?->value,
                        'is_active' => true,
                        'is_tradeable' => true,
                    ]);
                }
            }

            return $this->repository->searchStocks($query);
        } catch (\Exception $e) {
            return $dbResults;
        }
    }
}
