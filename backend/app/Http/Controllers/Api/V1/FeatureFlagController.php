<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Services\Features\FeatureFlagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Paper Trading Tycoon — Feature Flag Controller
 *
 * GET /api/v1/feature-flags
 *
 * Returns the feature flag state for the requesting user.
 * The Flutter app caches this payload on startup and refreshes on foreground.
 * Requires no authentication — flags apply to all users (with user-level
 * overrides evaluated by FeatureFlagService when a token is present).
 */
final class FeatureFlagController extends BaseApiController
{
    public function __construct(private readonly FeatureFlagService $featureFlagService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;

        $flags = $this->featureFlagService->getFlags(userId: $userId);

        return $this->success($flags);
    }
}
