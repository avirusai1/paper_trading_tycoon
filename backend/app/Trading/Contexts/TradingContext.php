<?php

declare(strict_types=1);

namespace App\Trading\Contexts;

use App\MarketData\DTOs\MarketStatus;
use App\MarketData\DTOs\StockQuote;
use App\Models\Holding;
use App\Models\Stock;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;

/**
 * Paper Trading Tycoon — Trading Context
 *
 * Immutable snapshot of all state required to validate and execute a trade.
 * Constructed once at pipeline initiation.
 */
final readonly class TradingContext
{
    /**
     * @param  Holding[]  $holdings  Array of holdings keyed by stock_id
     * @param  array<string, bool>  $featureFlags
     * @param  array<string, int>  $openSellQuantities  Mapping of stock_id -> total qty in open sell orders
     */
    public function __construct(
        public User $user,
        public Wallet $wallet,
        public array $holdings,
        public Stock $stock,
        public StockQuote $quote,
        public MarketStatus $marketStatus,
        public array $featureFlags,
        public bool $isPremium,
        public bool $isBanned,
        public int $openOrderExposurePaise,
        public array $openSellQuantities,
        public Carbon $builtAt,
    ) {}

    public function userId(): int
    {
        return $this->user->id;
    }

    public function virtualCash(): int
    {
        return $this->wallet->virtual_cash_paise;
    }

    public function getHolding(int $stockId): ?Holding
    {
        return $this->holdings[$stockId] ?? null;
    }

    public function getOpenSellQuantity(int $stockId): int
    {
        return $this->openSellQuantities[$stockId] ?? 0;
    }

    public function hasFeature(string $key): bool
    {
        return $this->featureFlags[$key] ?? false;
    }
}
