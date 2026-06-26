<?php

declare(strict_types=1);

namespace App\Portfolio\Contracts;

use App\Models\Wallet;
use App\Models\PortfolioSnapshot;
use Illuminate\Support\Collection;

/**
 * Interface PortfolioRepositoryContract
 *
 * Defines database access methods for loading user wallet, holdings, trades, and orders.
 */
interface PortfolioRepositoryContract
{
    /**
     * Retrieves the Wallet model for a given user.
     *
     * @param int $userId
     * @return Wallet
     */
    public function getWallet(int $userId): Wallet;

    /**
     * Retrieves all active holdings for a given user.
     * Active holdings are those with quantity > 0.
     *
     * @param int $userId
     * @return Collection
     */
    public function getActiveHoldings(int $userId): Collection;

    /**
     * Retrieves all open orders for a given user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getOpenOrders(int $userId): Collection;

    /**
     * Retrieves all executed trades for a given user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getTrades(int $userId): Collection;

    /**
     * Retrieves the latest portfolio snapshot for a given user.
     *
     * @param int $userId
     * @return PortfolioSnapshot|null
     */
    public function getLatestSnapshot(int $userId): ?PortfolioSnapshot;
}
