<?php

declare(strict_types=1);

namespace App\GameEngine\Enums;

/**
 * High-level player lifecycle states relevant to game engine decisions.
 * Derived from user status + premium flag when building GameContext.
 */
enum PlayerState: string
{
    case Active = 'active';
    case ActivePremium = 'active_premium';
    case Suspended = 'suspended';
    case Banned = 'banned';

    public function canParticipate(): bool
    {
        return match ($this) {
            self::Active, self::ActivePremium => true,
            default => false,
        };
    }

    public function isPremium(): bool
    {
        return $this === self::ActivePremium;
    }
}
