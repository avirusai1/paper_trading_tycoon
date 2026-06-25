<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

/**
 * Paper Trading Tycoon — Structured Logging Helper
 *
 * Enforces consistent log structure across all service and event classes.
 * All log entries include:
 *   - request_id   — from X-Request-ID header (injected by RequestIdMiddleware).
 *   - service      — class short name for rapid log filtering.
 *   - user_id      — where available, for per-user tracing.
 *
 * Never log: passwords, tokens, full card numbers, PII beyond user_id.
 * Log levels: error (needs attention), warning (recoverable), info (business event), debug (dev only).
 */
final class LogHelper
{
    /**
     * Log a business event at info level (trade executed, user registered, level up).
     *
     * @param  array<string, mixed>  $context
     */
    public static function businessEvent(string $event, array $context = []): void
    {
        Log::info($event, array_merge(['event_type' => $event], $context));
    }

    /**
     * Log a warning for recoverable issues (cache miss, retry, feature flag fallback).
     *
     * @param  array<string, mixed>  $context
     */
    public static function warning(string $message, array $context = []): void
    {
        Log::warning($message, $context);
    }

    /**
     * Log an error requiring investigation (failed queue listener, payment error).
     *
     * @param  array<string, mixed>  $context
     */
    public static function error(string $message, \Throwable $exception, array $context = []): void
    {
        Log::error($message, array_merge($context, [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]));
    }

    /**
     * Log at debug level — disabled in production via LOG_LEVEL env.
     *
     * @param  array<string, mixed>  $context
     */
    public static function debug(string $message, array $context = []): void
    {
        Log::debug($message, $context);
    }

    /**
     * Builds a standard log context array for service methods.
     *
     * @return array<string, mixed>
     */
    public static function context(string $service, ?int $userId = null, string $requestId = ''): array
    {
        return array_filter([
            'service' => $service,
            'user_id' => $userId,
            'request_id' => $requestId ?: request()->header('X-Request-ID', ''),
        ]);
    }
}
