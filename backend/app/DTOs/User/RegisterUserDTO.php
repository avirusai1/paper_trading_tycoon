<?php

declare(strict_types=1);

namespace App\DTOs\User;

/**
 * Paper Trading Tycoon — Register User Data Transfer Object
 *
 * Carries validated registration data from HTTP Request to AuthService.
 */
final readonly class RegisterUserDTO
{
    public function __construct(
        public readonly string $displayName,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $referralCode = null,
    ) {}
}
