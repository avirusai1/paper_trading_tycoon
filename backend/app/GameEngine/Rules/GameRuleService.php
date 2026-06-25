<?php
declare(strict_types=1);

namespace App\GameEngine\Rules;

use App\GameEngine\Contracts\GameRuleProviderContract;
use App\GameEngine\Exceptions\GameRuleNotFoundException;
use App\Models\GameRule;
use Illuminate\Support\Facades\Cache;

/**
 * Database-backed implementation of GameRuleProviderContract.
 *
 * All game balance values (XP amounts, coin rewards, thresholds, league
 * percentages, market hours, etc.) are stored in the `game_rules` table and
 * accessed through this service with cache-aside semantics.
 *
 * Cache Strategy:
 * - Individual rule: cache key "game_rule:{key}", TTL from GAME_RULES_CACHE_TTL
 *   env var (default 3600s / 1 hour).
 * - Groups: cache key "game_rules_group:{group}", same TTL.
 * - flush() clears all game_rule:* and game_rules_group:* keys via cache tags
 *   (requires a tag-capable driver: Redis / Memcached). Falls back to flushing
 *   the whole cache if tags are not supported.
 *
 * Zero hardcoded values — every constant must exist in the game_rules table.
 * If a required key is missing, GameRuleNotFoundException is thrown with a
 * clear message pointing to the seeder that needs to be re-run.
 */
final class GameRuleService implements GameRuleProviderContract
{
    private const CACHE_PREFIX = 'game_rule';
    private const GROUP_PREFIX = 'game_rules_group';
    private const CACHE_TAG    = 'game_rules';

    private int $cacheTtlSeconds;

    public function __construct()
    {
        $this->cacheTtlSeconds = (int) env('GAME_RULES_CACHE_TTL', 3600);
    }

    // ── GameRuleProviderContract ──────────────────────────────────────────────

    public function getInt(string $key, ?int $default = null): int
    {
        return (int) $this->resolve($key, $default);
    }

    public function getFloat(string $key, ?float $default = null): float
    {
        return (float) $this->resolve($key, $default);
    }

    public function getString(string $key, ?string $default = null): string
    {
        return (string) $this->resolve($key, $default);
    }

    public function getBool(string $key, ?bool $default = null): bool
    {
        $value = $this->resolve($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function getGroup(string $group): array
    {
        return $this->cached(self::GROUP_PREFIX . ':' . $group, function () use ($group): array {
            $rules = GameRule::where('group', $group)->get();
            $map   = [];

            foreach ($rules as $rule) {
                // Strip the "group." prefix from the key for the returned map
                $shortKey       = ltrim(str_replace($group . '.', '', $rule->key), '.');
                $map[$shortKey] = $rule->typedValue();
            }

            return $map;
        });
    }

    public function flush(): void
    {
        try {
            Cache::tags([self::CACHE_TAG])->flush();
        } catch (\BadMethodCallException) {
            // Cache driver does not support tags (e.g. file, array)
            Cache::flush();
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Resolve a rule value, hitting cache first.
     *
     * @throws GameRuleNotFoundException
     */
    private function resolve(string $key, mixed $default): mixed
    {
        return $this->cached(self::CACHE_PREFIX . ':' . $key, function () use ($key, $default): mixed {
            $rule = GameRule::where('key', $key)->first();

            if ($rule === null) {
                if ($default !== null) {
                    return $default;
                }
                throw GameRuleNotFoundException::forKey($key);
            }

            return $rule->typedValue();
        });
    }

    /**
     * Cache-aside helper.  Uses tags when available, falls back to plain cache.
     */
    private function cached(string $cacheKey, callable $loader): mixed
    {
        try {
            return Cache::tags([self::CACHE_TAG])
                ->remember($cacheKey, $this->cacheTtlSeconds, $loader);
        } catch (\BadMethodCallException) {
            return Cache::remember($cacheKey, $this->cacheTtlSeconds, $loader);
        }
    }
}
