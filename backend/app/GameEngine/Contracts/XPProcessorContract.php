<?php

declare(strict_types=1);

namespace App\GameEngine\Contracts;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\XPResult;
use App\GameEngine\Enums\XPSource;
use App\GameEngine\Exceptions\XPException;

/**
 * Contract for the XP processing subsystem.
 *
 * Responsibilities:
 * - Compute the XP amount to award for a given source (applying multipliers
 *   and daily caps from the Rules Engine).
 * - Persist the XP grant atomically (updating user_levels and inserting into
 *   xp_logs).
 * - Return a typed XPResult describing the outcome.
 *
 * Does NOT dispatch domain events — the caller (pipeline) is responsible.
 */
interface XPProcessorContract
{
    /**
     * Award XP to the user identified in the GameContext.
     *
     * @param  string  $sourceId  Idempotency key — unique per event (e.g. trade ID, challenge ID).
     *
     * @throws XPException
     */
    public function grant(
        GameContext $context,
        XPSource $source,
        string $sourceId,
        ?int $overrideAmount = null,
    ): XPResult;

    /**
     * Check how much XP has already been granted from a given source today.
     * Used to enforce daily caps without re-granting.
     */
    public function getDailyTotal(int $userId, XPSource $source): int;
}
