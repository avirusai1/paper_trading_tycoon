<?php

declare(strict_types=1);

namespace App\Portfolio\Contexts;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Holding;
use App\Models\PortfolioSnapshot;
use App\Models\UserLeague;
use App\Models\Season;
use App\MarketData\DTOs\StockQuote;
use App\MarketData\DTOs\MarketStatus;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Class PortfolioContext
 *
 * Immutable context snapshot representing all parameters required to valuate and analyze a user's portfolio.
 */
final readonly class PortfolioContext
{
    /**
     * PortfolioContext constructor.
     *
     * @param User $user
     * @param Wallet $wallet
     * @param array<int, Holding> $holdings Keyed by stock_id
     * @param array<string, StockQuote> $quotes Keyed by symbol
     * @param MarketStatus $marketStatus
     * @param PortfolioSnapshot|null $latestSnapshot
     * @param Collection $openOrders
     * @param Collection $trades
     * @param UserLeague|null $currentLeague
     * @param Season|null $currentSeason
     * @param array<string, bool> $featureFlags
     * @param Carbon $builtAt
     */
    public function __construct(
        public User $user,
        public Wallet $wallet,
        public array $holdings,
        public array $quotes,
        public MarketStatus $marketStatus,
        public ?PortfolioSnapshot $latestSnapshot,
        public Collection $openOrders,
        public Collection $trades,
        public ?UserLeague $currentLeague,
        public ?Season $currentSeason,
        public array $featureFlags,
        public Carbon $builtAt
    ) {}

    public function userId(): int
    {
        return $this->user->id;
    }

    public function cashPaise(): int
    {
        return $this->wallet->virtual_cash_paise;
    }

    public function holdingsCount(): int
    {
        return count($this->holdings);
    }

    public function getHolding(int $stockId): ?Holding
    {
        return $this->holdings[$stockId] ?? null;
    }

    public function getQuote(string $symbol): ?StockQuote
    {
        return $this->quotes[$symbol] ?? null;
    }

    public function hasFeature(string $key): bool
    {
        return $this->featureFlags[$key] ?? false;
    }
}
