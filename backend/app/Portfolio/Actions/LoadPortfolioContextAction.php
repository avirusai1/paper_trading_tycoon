<?php

declare(strict_types=1);

namespace App\Portfolio\Actions;

use App\MarketData\Services\MarketDataService;
use App\MarketData\ValueObjects\Exchange;
use App\MarketData\ValueObjects\Ticker;
use App\Models\Season;
use App\Models\User;
use App\Models\UserLeague;
use App\Services\Features\FeatureFlagService;
use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\Contracts\PortfolioRepositoryContract;
use App\Portfolio\Exceptions\PortfolioException;
use Carbon\Carbon;

/**
 * Class LoadPortfolioContextAction
 *
 * Resolves all database records, market prices, and config states to build the PortfolioContext.
 */
final readonly class LoadPortfolioContextAction
{
    public function __construct(
        private PortfolioRepositoryContract $portfolioRepository,
        private MarketDataService $marketDataService,
        private FeatureFlagService $featureFlagService
    ) {}

    /**
     * Loads portfolio data for the given user ID.
     *
     * @param int $userId
     * @return PortfolioContext
     * @throws PortfolioException
     */
    public function execute(int $userId): PortfolioContext
    {
        $user = User::query()->find($userId);
        if ($user === null) {
            throw new PortfolioException("User {$userId} not found.", 'user_not_found', 404);
        }

        $wallet = $this->portfolioRepository->getWallet($userId);
        $holdingsCollection = $this->portfolioRepository->getActiveHoldings($userId);
        
        $holdings = [];
        $quotes = [];

        foreach ($holdingsCollection as $holding) {
            $holdings[$holding->stock_id] = $holding;
            
            try {
                $ticker = new Ticker($holding->symbol);
                $quote = $this->marketDataService->getQuote($ticker);
                $quotes[$holding->symbol] = $quote;
            } catch (\Throwable $e) {
                // Keep moving, valuation will fall back to stored average/current value
            }
        }

        // Fetch market status for NSE
        $marketStatus = $this->marketDataService->getMarketStatus(new Exchange('NSE'));

        $latestSnapshot = $this->portfolioRepository->getLatestSnapshot($userId);
        $openOrders = $this->portfolioRepository->getOpenOrders($userId);
        $trades = $this->portfolioRepository->getTrades($userId);

        $activeSeason = Season::query()->active()->latest('starts_at')->first();
        $userLeague = null;
        if ($activeSeason !== null) {
            $userLeague = UserLeague::query()
                ->where('user_id', $userId)
                ->where('season_id', $activeSeason->id)
                ->first();
        }

        $flags = array_map(fn ($v) => (bool) $v, $this->featureFlagService->getFlags($userId));

        return new PortfolioContext(
            user: $user,
            wallet: $wallet,
            holdings: $holdings,
            quotes: $quotes,
            marketStatus: $marketStatus,
            latestSnapshot: $latestSnapshot,
            openOrders: $openOrders,
            trades: $trades,
            currentLeague: $userLeague,
            currentSeason: $activeSeason,
            featureFlags: $flags,
            builtAt: Carbon::now()
        );
    }
}
