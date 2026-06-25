<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Paper Trading Tycoon — Order Side Enum
 * Represents the direction of a trade order.
 */
enum OrderSide: string
{
    case Buy = 'buy';
    case Sell = 'sell';
}
