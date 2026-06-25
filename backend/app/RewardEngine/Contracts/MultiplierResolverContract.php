<?php
declare(strict_types=1);

namespace App\RewardEngine\Contracts;

use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\MultiplierType;

/**
 * Contract for resolving the effective reward multipliers for a given context.
 *
 * Multipliers are stacked multiplicatively:
 *   baseAmount × premiumMultiplier × weekendMultiplier × streakMultiplier × ...
 *
 * All multiplier values come from the Rules Engine — never hardcoded.
 */
interface MultiplierResolverContract
{
    /**
     * Resolve the total combined multiplier for the given type and context.
     * Returns a float ≥ 1.0 (no negative or zero multipliers).
     */
    public function resolve(MultiplierType $type, RewardContext $context): float;

    /**
     * Return the individual active multipliers broken down by type.
     * Used for audit logging and the rewards breakdown in API responses.
     *
     * @return array<string, float>  key = MultiplierType::value, value = multiplier
     */
    public function breakdown(RewardRequest $request, RewardContext $context): array;
}
