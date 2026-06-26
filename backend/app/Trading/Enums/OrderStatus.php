<?php

declare(strict_types=1);

namespace App\Trading\Enums;

/**
 * Paper Trading Tycoon — Order Status Enum
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Open = 'open';
    case PartiallyFilled = 'partially_filled';
    case Filled = 'filled';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
    case Expired = 'expired';
}
