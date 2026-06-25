<?php

declare(strict_types=1);

namespace App\Actions\Game;

use App\DTOs\Game\XPGrantDTO;

/**
 * Paper Trading Tycoon — Grant XP Action
 *
 * Awards XP to a user from a typed source. Enforces daily caps
 * via the Rules Engine. Publishes XPGranted on success.
 * Implementation: Milestone 5.
 */
final class GrantXPAction
{
    public function execute(XPGrantDTO $dto): void
    {
        // Implementation: Milestone 5
    }
}
