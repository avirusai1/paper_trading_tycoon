<?php
declare(strict_types=1);

namespace App\GameEngine\Support;

use App\GameEngine\Contexts\GameContext;

/**
 * Calculates the effective XP multiplier for a user at a given point in time.
 *
 * Multiplier sources (stacked multiplicatively, not additively):
 * 1. Base: 1.0 (always applied)
 * 2. Premium boost: from game rule 'xp.premium_multiplier' (default 1.5)
 * 3. Login streak bonus: tiered from game rule 'xp.streak_multiplier_{tier}'
 * 4. Equipped store items with 'xp_boost' effect
 *
 * The final multiplier is capped at the game rule 'xp.max_multiplier' (default 3.0).
 */
final class XPMultiplierCalculator
{
    public function __construct(
        private readonly \App\GameEngine\Contracts\GameRuleProviderContract $rules,
    ) {}

    /**
     * Calculate the total XP multiplier for the given context.
     * Returns a float ≥ 1.0.
     */
    public function calculate(GameContext $context): float
    {
        $multiplier = 1.0;

        // Premium boost
        if ($context->isPremium()) {
            $multiplier *= $this->rules->getFloat('xp.premium_multiplier', 1.5);
        }

        // Login streak boost
        $multiplier *= $this->streakMultiplier($context->loginStreakDays);

        // Equipped item boosts (from activeMultipliers, which the context builder sets)
        if (isset($context->activeMultipliers['xp'])) {
            $multiplier *= (float) $context->activeMultipliers['xp'];
        }

        // Cap
        $cap = $this->rules->getFloat('xp.max_multiplier', 3.0);
        return min($multiplier, $cap);
    }

    private function streakMultiplier(int $streakDays): float
    {
        return match (true) {
            $streakDays >= 30 => $this->rules->getFloat('xp.streak_multiplier_30', 1.3),
            $streakDays >= 7  => $this->rules->getFloat('xp.streak_multiplier_7',  1.2),
            $streakDays >= 3  => $this->rules->getFloat('xp.streak_multiplier_3',  1.1),
            default           => 1.0,
        };
    }
}
