<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\DTOs\User\RegisterUserDTO;

/**
 * Paper Trading Tycoon — Register User Action
 *
 * Creates user record, fires UserRegistered event.
 * Portfolio wallet creation and initial league assignment
 * happen via event listeners (not directly in this action).
 * Implementation: Milestone 2.
 */
final class RegisterUserAction
{
    public function execute(RegisterUserDTO $dto): void
    {
        // Implementation: Milestone 2
    }
}
