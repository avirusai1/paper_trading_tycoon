<?php

declare(strict_types=1);

namespace App\Trading\Factories;

use App\Enums\OrderSide;
use App\MarketData\Services\MarketDataService;
use App\MarketData\ValueObjects\Exchange;
use App\MarketData\ValueObjects\Ticker;
use App\Models\Holding;
use App\Models\Order;
use App\Models\Stock;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Features\FeatureFlagService;
use App\Trading\Contexts\TradingContext;
use App\Trading\Contracts\TradingContextFactoryContract;
use App\Trading\DTOs\TradeRequest;
use App\Trading\Enums\TradingValidationReason;
use App\Trading\Exceptions\TradingException;
use App\Trading\Exceptions\TradingValidationException;
use Carbon\Carbon;

/**
 * Hydrates and constructs the immutable TradingContext for a TradeRequest.
 */
final readonly class TradingContextFactory implements TradingContextFactoryContract
{
    public function __construct(
        private MarketDataService $marketDataService,
        private FeatureFlagService $featureFlags,
    ) {}

    /**
     * @throws TradingException
     * @throws TradingValidationException
     */
    public function build(TradeRequest $request): TradingContext
    {
        // 1. Fetch User
        $user = User::query()->find($request->userId);
        if ($user === null) {
            throw new TradingException(
                "User {$request->userId} not found.",
                'user_not_found',
                404
            );
        }

        // 2. Fetch Stock
        $stock = Stock::query()
            ->where('id', $request->stockId)
            ->orWhere('symbol', $request->symbol)
            ->first();

        if ($stock === null) {
            throw new TradingValidationException(
                TradingValidationReason::InvalidSymbol,
                "Stock with ID {$request->stockId} or symbol '{$request->symbol}' not found."
            );
        }

        // 3. Fetch Quote and Market Status
        $ticker = new Ticker($stock->symbol);
        $exchange = new Exchange($stock->exchange);

        $quote = $this->marketDataService->getQuote($ticker);
        $marketStatus = $this->marketDataService->getMarketStatus($exchange);

        // 4. Fetch Wallet
        $wallet = Wallet::query()
            ->where('user_id', $request->userId)
            ->first();

        if ($wallet === null) {
            throw new TradingException(
                "Wallet for user {$request->userId} not found.",
                'wallet_not_found',
                404
            );
        }

        // 5. Fetch Holdings
        $holdings = Holding::query()
            ->where('user_id', $request->userId)
            ->get()
            ->keyBy('stock_id')
            ->all();

        // 6. Fetch Open Order Buy Exposure
        $openOrders = Order::query()
            ->open()
            ->where('user_id', $request->userId)
            ->where('side', OrderSide::Buy)
            ->get();

        $openOrderExposure = 0;
        foreach ($openOrders as $order) {
            $price = $order->limit_price_paise ?? $order->stop_price_paise ?? 0;
            $openOrderExposure += $order->remainingQuantity() * $price;
        }

        // 7. Fetch Open Sell Quantities
        $openSellQuantities = Order::query()
            ->open()
            ->where('user_id', $request->userId)
            ->where('side', OrderSide::Sell)
            ->groupBy('stock_id')
            ->selectRaw('stock_id, SUM(quantity - filled_quantity) as open_qty')
            ->pluck('open_qty', 'stock_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        // 8. Resolve feature flags and premium
        $flags = array_map(fn ($v) => (bool) $v, $this->featureFlags->getFlags($request->userId));
        $isPremium = (bool) ($user->is_premium ?? false);
        $isBanned = $user->isBanned();

        return new TradingContext(
            user: $user,
            wallet: $wallet,
            holdings: $holdings,
            stock: $stock,
            quote: $quote,
            marketStatus: $marketStatus,
            featureFlags: $flags,
            isPremium: $isPremium,
            isBanned: $isBanned,
            openOrderExposurePaise: $openOrderExposure,
            openSellQuantities: $openSellQuantities,
            builtAt: Carbon::now(),
        );
    }
}
