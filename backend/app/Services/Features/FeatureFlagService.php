<?php

declare(strict_types=1);

namespace App\Services\Features;

use App\Services\BaseService;
use Illuminate\Support\Facades\Cache;

/**
 * Paper Trading Tycoon — Feature Flag Service
 *
 * Reads feature flags from database (admin-managed) with a config cache fallback.
 * Evaluates user-level overrides for premium gating and percentage rollout.
 *
 * Flag values:
 *   - false      → disabled for everyone
 *   - true       → enabled for everyone
 *   - 'premium'  → enabled only for premium subscribers
 *   - 0–100 int  → percentage rollout; deterministic hash of user ID
 */
final class FeatureFlagService extends BaseService
{
    /**
     * Returns the evaluated feature flag map for the given user.
     *
     * @return array<string, bool|string>
     */
    public function getFlags(?int $userId = null): array
    {
        $raw = $this->loadRawFlags();

        if ($userId === null) {
            return array_map(
                static fn (mixed $v): bool|string => $v === 'premium' ? false : (bool) $v,
                $raw,
            );
        }

        $evaluated = [];
        foreach ($raw as $key => $value) {
            $evaluated[$key] = $this->evaluateForUser($value, $userId);
        }

        return $evaluated;
    }

    /**
     * Checks whether a single flag is enabled for the given user.
     */
    public function isEnabled(string $flagKey, ?int $userId = null): bool
    {
        $flags = $this->getFlags($userId);

        return (bool) ($flags[$flagKey] ?? false);
    }

    /**
     * Invalidates the flag cache — called by admin panel on flag changes.
     */
    public function invalidateCache(): void
    {
        Cache::forget(config('feature_flags.cache_key', 'feature_flags_payload'));
    }

    /**
     * @return array<string, mixed>
     */
    private function loadRawFlags(): array
    {
        $ttl = config('feature_flags.cache_ttl_seconds', 300);
        $cacheKey = config('feature_flags.cache_key', 'feature_flags_payload');

        return Cache::remember($cacheKey, $ttl, static function (): array {
            return config('feature_flags.defaults', []);
        });
    }

    private function evaluateForUser(mixed $value, int $userId): bool|string
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === 'premium') {
            return 'premium';
        }

        if (is_int($value) && $value >= 0 && $value <= 100) {
            $hash = abs(crc32("flag_{$userId}")) % 100;

            return $hash < $value;
        }

        return false;
    }
}
