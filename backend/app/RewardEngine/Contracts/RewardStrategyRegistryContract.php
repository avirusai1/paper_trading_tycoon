<?php
declare(strict_types=1);

namespace App\RewardEngine\Contracts;

use App\RewardEngine\Enums\RewardType;

/**
 * Registry contract for strategy lookup by reward type.
 *
 * Decouples the pipeline from concrete strategy classes. New strategies
 * register themselves here without touching the pipeline.
 */
interface RewardStrategyRegistryContract
{
    /**
     * Retrieve the strategy for the given reward type.
     *
     * @throws \App\RewardEngine\Exceptions\RewardEngineException  If no strategy registered.
     */
    public function get(RewardType $type): RewardStrategyContract;

    /**
     * Return true if a strategy is registered for the given type.
     */
    public function has(RewardType $type): bool;
}
