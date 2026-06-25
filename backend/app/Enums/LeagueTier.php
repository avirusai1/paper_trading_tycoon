<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Paper Trading Tycoon — League Tier Enum
 * Competitive leagues in ascending rank order.
 */
enum LeagueTier: string
{
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
    case Platinum = 'platinum';
    case Diamond = 'diamond';

    /**
     * Returns the numeric rank (1 = lowest, 5 = highest).
     */
    public function rank(): int
    {
        return match($this) {
            self::Bronze   => 1,
            self::Silver   => 2,
            self::Gold     => 3,
            self::Platinum => 4,
            self::Diamond  => 5,
        };
    }

    /**
     * Returns the next league tier for promotion, or null if already Diamond.
     */
    public function next(): ?self
    {
        return match($this) {
            self::Bronze   => self::Silver,
            self::Silver   => self::Gold,
            self::Gold     => self::Platinum,
            self::Platinum => self::Diamond,
            self::Diamond  => null,
        };
    }

    /**
     * Returns the previous league tier for demotion, or null if already Bronze.
     */
    public function previous(): ?self
    {
        return match($this) {
            self::Bronze   => null,
            self::Silver   => self::Bronze,
            self::Gold     => self::Silver,
            self::Platinum => self::Gold,
            self::Diamond  => self::Platinum,
        };
    }
}
