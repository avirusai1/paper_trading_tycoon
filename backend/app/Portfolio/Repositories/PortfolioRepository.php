<?php

declare(strict_types=1);

namespace App\Portfolio\Repositories;

use App\Models\Holding;
use App\Models\Order;
use App\Models\PortfolioSnapshot;
use App\Models\Trade;
use App\Models\Wallet;
use App\Portfolio\Contracts\PortfolioRepositoryContract;
use Illuminate\Support\Collection;

/**
 * Class PortfolioRepository
 *
 * Eloquent implementation of PortfolioRepositoryContract.
 */
final class PortfolioRepository implements PortfolioRepositoryContract
{
    public function getWallet(int $userId): Wallet
    {
        /** @var Wallet */
        return Wallet::query()->where('user_id', $userId)->firstOrFail();
    }

    public function getActiveHoldings(int $userId): Collection
    {
        return Holding::query()
            ->where('user_id', $userId)
            ->active()
            ->with('stock')
            ->get();
    }

    public function getOpenOrders(int $userId): Collection
    {
        return Order::query()
            ->where('user_id', $userId)
            ->open()
            ->get();
    }

    public function getTrades(int $userId): Collection
    {
        return Trade::query()
            ->where('user_id', $userId)
            ->get();
    }

    public function getLatestSnapshot(int $userId): ?PortfolioSnapshot
    {
        /** @var PortfolioSnapshot|null */
        return PortfolioSnapshot::query()
            ->where('user_id', $userId)
            ->latest('taken_at')
            ->first();
    }
}
