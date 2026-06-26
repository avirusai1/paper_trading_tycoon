<?php

declare(strict_types=1);

namespace App\Trading\Enums;

/**
 * Paper Trading Tycoon — Trading Validation Failure Reason
 */
enum TradingValidationReason: string
{
    case MarketClosed = 'market_closed';
    case OutsideTradingHours = 'outside_trading_hours';
    case InsufficientFunds = 'insufficient_funds';
    case InsufficientHoldings = 'insufficient_holdings';
    case MaxPositionsExceeded = 'max_positions_exceeded';
    case MaxDailyTradesExceeded = 'max_daily_trades_exceeded';
    case MaxExposureExceeded = 'max_exposure_exceeded';
    case DuplicateOrder = 'duplicate_order';
    case OrderExpired = 'order_expired';
    case InvalidQuantity = 'invalid_quantity';
    case InvalidSymbol = 'invalid_symbol';
    case FeatureDisabled = 'feature_disabled';
    case PremiumOnly = 'premium_only';
    case UserBanned = 'user_banned';
    case InvalidOrderType = 'invalid_order_type';
    case InvalidPrice = 'invalid_price';
}
