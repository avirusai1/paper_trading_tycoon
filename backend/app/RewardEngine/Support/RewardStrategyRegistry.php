<?php

declare(strict_types=1);

namespace App\RewardEngine\Support;

use App\RewardEngine\Contracts\RewardStrategyContract;
use App\RewardEngine\Contracts\RewardStrategyRegistryContract;
use App\RewardEngine\Enums\RewardType;
use App\RewardEngine\Exceptions\RewardEngineException;

/**
 * Strategy registry — maps RewardType → RewardStrategyContract.
 *
 * Strategies are registered via the constructor. New types plug in by adding
 * a new strategy and registering it in AppServiceProvider without touching
 * the pipeline (Open/Closed Principle).
 */
final class RewardStrategyRegistry implements RewardStrategyRegistryContract
{
    /**
     * @var array<string, RewardStrategyContract>
     */
    private array $strategies = [];

    /**
     * @param  RewardStrategyContract[]  $strategies
     */
    public function __construct(array $strategies = [])
    {
        foreach ($strategies as $strategy) {
            $this->register($strategy);
        }
    }

    public function register(RewardStrategyContract $strategy): void
    {
        $this->strategies[$strategy->handles()->value] = $strategy;
    }

    public function get(RewardType $type): RewardStrategyContract
    {
        if (! $this->has($type)) {
            throw new RewardEngineException(
                "No strategy registered for RewardType '{$type->value}'.",
                'reward_no_strategy_registered',
            );
        }

        return $this->strategies[$type->value];
    }

    public function has(RewardType $type): bool
    {
        return isset($this->strategies[$type->value]);
    }
}
