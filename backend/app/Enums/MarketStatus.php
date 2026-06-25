<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Paper Trading Tycoon — Market Status Enum
 * Current state of the NSE/BSE trading session.
 */
enum MarketStatus: string
{
    case Open       = 'open';
    case Closed     = 'closed';
    case PreMarket  = 'pre_market';
    case PostMarket = 'post_market';
    case Holiday    = 'holiday';
}
