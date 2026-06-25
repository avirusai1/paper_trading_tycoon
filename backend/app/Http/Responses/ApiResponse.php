<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

/**
 * Paper Trading Tycoon — API Response Builder
 *
 * Enforces the standard API response envelope for all endpoints:
 *
 * Success:
 *   { "success": true, "data": {...}, "message": "..." }
 *
 * Paginated:
 *   { "success": true, "data": [...], "meta": { "current_page": 1, ... }, "message": "..." }
 *
 * Error:
 *   { "success": false, "message": "...", "errors": { "field": ["..."] } }
 *
 * All API responses must use this class. No bare arrays or objects at root level.
 * Timestamps are ISO 8601 UTC. Monetary values are paise integers.
 */
final class ApiResponse
{
    /**
     * Build a successful single-resource response.
     *
     * @param  mixed  $data  Array, API Resource, or null.
     */
    public static function success(mixed $data = null, string $message = '', int $status = 200): JsonResponse
    {
        $payload = ['success' => true];

        if ($message !== '') {
            $payload['message'] = $message;
        }

        $payload['data'] = $data;

        return response()->json($payload, $status);
    }

    /**
     * Build a paginated collection response.
     */
    public static function paginated(LengthAwarePaginator $paginator, string $message = ''): JsonResponse
    {
        $payload = [
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];

        if ($message !== '') {
            $payload['message'] = $message;
        }

        return response()->json($payload, 200);
    }

    /**
     * Build an error response.
     *
     * @param  array<string, string[]>  $errors  Field-level validation errors.
     */
    public static function error(
        string $message,
        int $status = 400,
        array $errors = [],
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
