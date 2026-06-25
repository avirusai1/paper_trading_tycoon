<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paper Trading Tycoon — Request ID Middleware
 *
 * Assigns a unique X-Request-ID to every incoming request and echoes
 * it back in the response headers. This ID is included in all log entries
 * (via LogHelper) enabling complete request tracing in production logs.
 *
 * If the client sends an X-Request-ID, it is accepted as-is (useful for
 * correlating mobile client logs with server logs in support investigations).
 */
final class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-ID') ?? Str::uuid()->toString();

        // Store in request for access by LogHelper throughout the request lifecycle.
        $request->headers->set('X-Request-ID', $requestId);

        $response = $next($request);

        // Echo the ID back in the response for client-side correlation.
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
