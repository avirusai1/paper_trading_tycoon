<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Paper Trading Tycoon — Health Check Controller
 *
 * GET /api/health
 *
 * Returns system health status for:
 *   - API server responsiveness
 *   - Database connectivity
 *
 * Used by Hostinger cron monitoring and CI pipeline smoke tests.
 * This is the exit criterion for Milestone 1 (staging deployment).
 */
final class HealthController
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'api' => true,
            'database' => $this->checkDatabase(),
        ];

        $isHealthy = ! in_array(false, $checks, true);

        return ApiResponse::success(
            data: [
                'status' => $isHealthy ? 'healthy' : 'degraded',
                'checks' => $checks,
                'timestamp' => now()->toIso8601ZuluString(),
                'version' => config('app.version', '1.0.0'),
            ],
            status: $isHealthy ? 200 : 503,
        );
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
