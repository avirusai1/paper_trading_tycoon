<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Paper Trading Tycoon — Achievement Tier Enum
 * Rarity tiers for the achievement reward system.
 */
enum AchievementTier: string
{
    case Bronze   = 'bronze';
    case Silver   = 'silver';
    case Gold     = 'gold';
    case Platinum = 'platinum';
    case Hidden   = 'hidden'; // Revealed only when unlocked
}
