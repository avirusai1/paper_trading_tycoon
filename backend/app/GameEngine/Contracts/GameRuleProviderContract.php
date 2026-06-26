<?php

declare(strict_types=1);

namespace App\GameEngine\Contracts;

use App\GameEngine\Exceptions\GameRuleNotFoundException;

/**
 * Contract for reading game balance rules.
 *
 * All numeric values used in game logic (XP amounts, coin rewards, thresholds)
 * must be retrieved through this contract — never hardcoded in services or
 * actions.  Implementations are expected to apply cache-aside to avoid a DB
 * read on every gameplay event.
 */
interface GameRuleProviderContract
{
    /**
     * Return the integer value of a game rule.
     * Throws if the key is not found and no default is supplied.
     *
     * @throws GameRuleNotFoundException
     */
    public function getInt(string $key, ?int $default = null): int;

    /**
     * Return the float value of a game rule.
     *
     * @throws GameRuleNotFoundException
     */
    public function getFloat(string $key, ?float $default = null): float;

    /**
     * Return the string value of a game rule.
     *
     * @throws GameRuleNotFoundException
     */
    public function getString(string $key, ?string $default = null): string;

    /**
     * Return the boolean value of a game rule.
     *
     * @throws GameRuleNotFoundException
     */
    public function getBool(string $key, ?bool $default = null): bool;

    /**
     * Return all rules belonging to a named group.
     * Key is the short key within the group, value is already typed.
     *
     * @return array<string, mixed>
     */
    public function getGroup(string $group): array;

    /**
     * Flush any cached rules, forcing re-read on next access.
     * Used after an admin updates balance values.
     */
    public function flush(): void;
}
