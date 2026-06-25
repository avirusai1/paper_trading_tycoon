<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Paper Trading Tycoon — AchievementResource
 *
 * Transforms the Eloquent model or DTO into the API response shape.
 * All responses follow the ApiResponse envelope structure.
 * Monetary values are returned as paise integers.
 * Timestamps are ISO 8601 UTC strings.
 *
 * Implementation: Per feature milestone.
 */
final class AchievementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Implementation per milestone.
        return parent::toArray($request);
    }
}
