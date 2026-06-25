<?php
declare(strict_types=1);

namespace App\GameEngine\Exceptions;

/**
 * Thrown when a required game rule key is not found in the database
 * and no default was provided.
 */
final class GameRuleNotFoundException extends GameEngineException
{
    public static function forKey(string $key): self
    {
        return new self(
            "Game rule '{$key}' not found. Ensure GameRulesSeeder has been run.",
            'game_rule_not_found',
        );
    }
}
