<?php
declare(strict_types=1);

namespace App\GameEngine\Contracts;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\CareerResult;

/**
 * Contract for the Career Title progression subsystem.
 *
 * Determines whether the user's current level places them in a new career
 * title tier. Persists the title change and returns the result.
 */
interface CareerProcessorContract
{
    /**
     * Evaluate the user's current level and update their career title if it
     * has changed. Returns the result regardless of whether a change occurred.
     *
     * @throws \App\GameEngine\Exceptions\CareerException
     */
    public function evaluate(GameContext $context): CareerResult;
}
