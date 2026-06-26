<?php

declare(strict_types=1);

namespace App\Portfolio\Enums;

/**
 * Enum PortfolioValidationReason
 *
 * Reasons for portfolio calculation or snapshot validation failures.
 */
enum PortfolioValidationReason: string
{
    case InvalidSnapshotData = 'invalid_snapshot_data';
    case NegativeWalletBalance = 'negative_wallet_balance';
    case HoldingInconsistency = 'holding_inconsistency';
    case MarketDataStale = 'market_data_stale';
    case PortfolioIntegrityFailure = 'portfolio_integrity_failure';
}
