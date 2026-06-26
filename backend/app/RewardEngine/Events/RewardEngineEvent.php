<?php

declare(strict_types=1);

namespace App\RewardEngine\Events;

/**
 * Base marker for all Reward Engine domain events.
 *
 * These are internal events fired within the pipeline. They are separate from
 * the application-level App\Events\* which go to the Laravel event bus.
 * Listeners should implement ShouldQueue.
 */
abstract class RewardEngineEvent
{
    public readonly float $occurredAt;

    public function __construct()
    {
        $this->occurredAt = microtime(true);
    }
}
