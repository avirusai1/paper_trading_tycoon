<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Paper Trading Tycoon — Global Exception Handler
 *
 * Converts exceptions into consistent API response envelopes.
 * Maps exception types to appropriate HTTP status codes.
 * Logs 500-level errors with stack traces; returns generic messages to clients.
 * Never exposes internal implementation details in error responses.
 */
class Handler extends ExceptionHandler
{
    /**
     * Exception types that should not be reported to the log.
     *
     * @var array<class-string<Throwable>>
     */
    protected $dontReport = [
        // Business exceptions are expected and shouldn't fill logs with noise.
    ];

    /**
     * Exception types that should not be flashed to the session on validation errors.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register exception handling callbacks.
     */
    public function register(): void
    {
        $this->reportable(static function (Throwable $e): void {
            // Future: forward to error tracking service (Sentry, Bugsnag) in production.
        });
    }

    /**
     * Render all exceptions as consistent API envelopes for API routes.
     */
    public function render($request, Throwable $e): mixed
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->renderApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Maps exceptions to JSON API responses.
     */
    private function renderApiException(Request $request, Throwable $e): JsonResponse
    {
        // 422 Validation errors — include field-level messages.
        if ($e instanceof ValidationException) {
            return ApiResponse::error(
                message: $e->getMessage(),
                status: 422,
                errors: $e->errors(),
            );
        }

        // 401 Unauthenticated.
        if ($e instanceof AuthenticationException) {
            return ApiResponse::error(
                message: 'Unauthenticated. Please log in.',
                status: 401,
            );
        }

        // 4xx and 5xx HTTP exceptions.
        if ($e instanceof HttpException) {
            return ApiResponse::error(
                message: $e->getMessage() ?: 'HTTP error.',
                status: $e->getStatusCode(),
            );
        }

        // 500 Internal Server Error — log the full exception, return generic message.
        report($e);

        return ApiResponse::error(
            message: 'An unexpected server error occurred. Our team has been notified.',
            status: 500,
        );
    }
}
