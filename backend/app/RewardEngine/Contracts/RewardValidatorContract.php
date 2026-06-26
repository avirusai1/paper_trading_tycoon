<?php

declare(strict_types=1);

namespace App\RewardEngine\Contracts;

use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Exceptions\RewardValidationException;

/**
 * Contract for a single validation rule in the reward validator chain.
 *
 * Validators form a chain-of-responsibility. Each validator either:
 * - Passes (returns void): the next validator in chain is called.
 * - Throws RewardValidationException: pipeline aborts with the reason.
 *
 * Validators must be stateless and fast — no DB writes.
 */
interface RewardValidatorContract
{
    /**
     * Validate the request against this rule.
     *
     * @throws RewardValidationException If validation fails.
     */
    public function validate(RewardRequest $request, RewardContext $context): void;
}
