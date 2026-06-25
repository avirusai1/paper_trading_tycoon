<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Paper Trading Tycoon — Coin Transaction Source Enum
 *
 * Every coin ledger entry carries a typed source.
 * This enables per-source fraud detection and audit queries.
 * See ADR-004 and 00_MASTER_ARCHITECTURE.md Section 3.10.
 */
enum CoinTransactionSource: string
{
    case Challenge    = 'challenge';
    case Achievement  = 'achievement';
    case LevelUp      = 'level_up';
    case Referral     = 'referral';
    case DailyLogin   = 'daily_login';
    case SeasonReward = 'season_reward';
    case AdminGrant   = 'admin_grant';
    case StorePurchase = 'store_purchase'; // Debit
    case Refund       = 'refund';          // Compensating transaction
}
