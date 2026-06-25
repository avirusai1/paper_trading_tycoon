<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paper Trading Tycoon — Email Verification Middleware
 *
 * Blocks access to trading and portfolio endpoints until the user
 * has verified their email address. Applied to trading routes as
 * an additional guardrail against fraudulent bot-registration abuse.
 *
 * Returns HTTP 403 with a machine-readable code so the Flutter app
 * can route users to the email verification screen.
 */
final class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() !== null && ! $request->user()->hasVerifiedEmail()) {
            return ApiResponse::error(
                message: 'Email verification required. Please verify your email address before trading.',
                status: 403,
            );
        }

        return $next($request);
    }
}
