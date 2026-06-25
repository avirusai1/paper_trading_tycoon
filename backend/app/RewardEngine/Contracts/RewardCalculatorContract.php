<?php
declare(strict_types=1);

namespace App\RewardEngine\Contracts;

use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\RewardRequest;

/**
 * Contract for the reward amount calculator.
 *
 * Receives a validated request and context, queries the Rules Engine for base
 * amounts, resolves all applicable multipliers, and returns a CalculatedReward.
 *
 * No DB writes — pure calculation.
 */
interface RewardCalculatorContract
{
    /**
     * @throws \App\RewardEngine\Exceptions\RewardCalculationException
     */
    public function calculate(RewardRequest $request, RewardContext $context): CalculatedReward;
}
