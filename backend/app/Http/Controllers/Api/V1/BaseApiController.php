<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

/**
 * Base API Controller for v1 endpoints.
 *
 * All v1 controllers extend this class to inherit consistent response
 * formatting via ApiResponse helpers. Never add business logic here —
 * controllers delegate all work to service classes or action classes.
 */
abstract class BaseApiController extends Controller
{
    /**
     * Return a standardised success response with a single resource.
     *
     * @param  mixed  $data  The response payload (array or Resource).
     */
    protected function success(mixed $data = null, string $message = '', int $status = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $status);
    }

    /**
     * Return a standardised 201 Created response.
     */
    protected function created(mixed $data = null, string $message = 'Resource created successfully.'): JsonResponse
    {
        return ApiResponse::success($data, $message, 201);
    }

    /**
     * Return a standardised 204 No Content response.
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return a standardised paginated collection response.
     *
     * @param  LengthAwarePaginator  $paginator
     */
    protected function paginated(mixed $paginator, string $message = ''): JsonResponse
    {
        return ApiResponse::paginated($paginator, $message);
    }
}
