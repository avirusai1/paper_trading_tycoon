<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Paper Trading Tycoon — Authentication Controller
 *
 * Handles all authentication lifecycle endpoints.
 * Business logic is delegated to AuthService (Milestone 2).
 *
 * Routes:
 *   POST   /api/v1/auth/register         — New user registration
 *   POST   /api/v1/auth/login            — Email/password login
 *   POST   /api/v1/auth/logout           — Revoke current token
 *   POST   /api/v1/auth/refresh          — Silent token refresh
 *   POST   /api/v1/auth/password/forgot  — Send password reset email
 *   POST   /api/v1/auth/password/reset   — Complete password reset
 *   POST   /api/v1/auth/email/resend     — Resend verification email
 *   GET    /api/v1/auth/email/verify     — Verify email via signed URL
 */
final class AuthController extends BaseApiController
{
    public function register(Request $request): JsonResponse
    {
        // Implementation: Milestone 2
        return $this->success(null, 'Registration endpoint — Milestone 2.');
    }

    public function login(Request $request): JsonResponse
    {
        // Implementation: Milestone 2
        return $this->success(null, 'Login endpoint — Milestone 2.');
    }

    public function logout(Request $request): JsonResponse
    {
        // Implementation: Milestone 2
        return $this->noContent();
    }

    public function refresh(Request $request): JsonResponse
    {
        // Implementation: Milestone 2
        return $this->success(null, 'Refresh endpoint — Milestone 2.');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        // Implementation: Milestone 2
        return $this->success(null, 'Forgot password endpoint — Milestone 2.');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        // Implementation: Milestone 2
        return $this->success(null, 'Reset password endpoint — Milestone 2.');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        // Implementation: Milestone 2
        return $this->success(null, 'Resend verification endpoint — Milestone 2.');
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse
    {
        // Implementation: Milestone 2
        return $this->success(null, 'Email verification endpoint — Milestone 2.');
    }
}
