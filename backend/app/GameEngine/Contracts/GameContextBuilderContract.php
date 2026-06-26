<?php

declare(strict_types=1);

namespace App\GameEngine\Contracts;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\Exceptions\GameEngineException;

/**
 * Contract for assembling a complete GameContext from persisted state.
 *
 * Implementations query the database (via repositories) and produce a fully
 * hydrated, immutable GameContext snapshot. Caching is the responsibility of
 * the implementation, not the caller.
 */
interface GameContextBuilderContract
{
    /**
     * Build and return the current GameContext for the given user.
     *
     * @throws GameEngineException If the user or required data cannot be loaded.
     */
    public function build(int $userId): GameContext;
}
