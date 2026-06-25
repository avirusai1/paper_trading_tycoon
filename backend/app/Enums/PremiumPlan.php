<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Paper Trading Tycoon — Premium Plan Enum
 * Available premium subscription plans.
 */
enum PremiumPlan: string
{
    case Monthly = 'monthly';
    case Annual  = 'annual';

    /**
     * Duration in days for this plan.
     */
    public function durationDays(): int
    {
        return match($this) {
            self::Monthly => 30,
            self::Annual  => 365,
        };
    }
}
