<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paper Trading Tycoon — Idempotency Middleware
 *
 * Enforces idempotency on state-mutating API endpoints (trades, coin awards).
 * If the client sends a duplicate request with the same Idempotency-Key
 * within the TTL window, returns the cached response without re-executing.
 *
 * Anti-cheat reliance: duplicate trade submissions are the primary vector
 * for virtual cash exploitation. This middleware is the first line of defence.
 *
 * Usage: Apply to routes that require idempotency:
 *   Route::post('/trades/buy', ...)->middleware('idempotency');
 */
final class IdempotencyMiddleware
{
    private const CACHE_PREFIX = 'idempotency_';
    private const TTL_SECONDS = 86400; // 24 hours

    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');

        if (! $key) {
            return ApiResponse::error(
                message: 'Idempotency-Key header is required for this request.',
                status: 400,
            );
        }

        $userId = $request->user()?->id ?? 'guest';
        $cacheKey = self::CACHE_PREFIX.$userId.'_'.$key;

        // Return cached response for duplicate requests within TTL window.
        if (Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);

            return response()->json($cachedData['body'], $cachedData['status'])
                ->header('Idempotency-Replayed', 'true');
        }

        $response = $next($request);

        // Cache successful responses only (2xx). Do not cache errors.
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            Cache::put($cacheKey, [
                'status' => $response->getStatusCode(),
                'body' => json_decode($response->getContent(), true),
            ], self::TTL_SECONDS);
        }

        return $response;
    }
}
