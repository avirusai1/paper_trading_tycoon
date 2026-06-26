<?php

declare(strict_types=1);

namespace App\Trading\Enums;

/**
 * Paper Trading Tycoon — Order Type Enum
 */
enum OrderType: string
{
    case Market = 'market';
    case Limit = 'limit';
    case Stop = 'stop';
    case StopLimit = 'stop_limit';
    case Bracket = 'bracket';
}
