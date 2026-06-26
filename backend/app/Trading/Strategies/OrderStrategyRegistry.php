<?php

declare(strict_types=1);

namespace App\Trading\Strategies;

use App\Trading\Contracts\OrderExecutionStrategyContract;
use App\Trading\Enums\OrderType;
use InvalidArgumentException;

/**
 * Strategy Registry mapping OrderType to OrderExecutionStrategyContract.
 */
final class OrderStrategyRegistry
{
    /**
     * @param  array<string, OrderExecutionStrategyContract>  $strategies
     */
    public function __construct(
        private array $strategies
    ) {}

    public function resolve(OrderType $type): OrderExecutionStrategyContract
    {
        // StopLimit shares the stop-loss strategy logic for trigger matching
        $key = $type === OrderType::StopLimit ? OrderType::Stop->value : $type->value;

        if (! isset($this->strategies[$key])) {
            throw new InvalidArgumentException("No order execution strategy registered for type: {$type->value}");
        }

        return $this->strategies[$key];
    }
}
