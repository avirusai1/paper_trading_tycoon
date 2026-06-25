<?php
declare(strict_types=1);

namespace App\RewardEngine\Contracts;

use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\RewardRequest;

/**
 * Contract for assembling a RewardContext from a RewardRequest.
 *
 * Loads all player state needed by the pipeline (wallet, XP, level, league,
 * feature flags, multipliers) from the database or cache.
 *
 * Returns an immutable RewardContext snapshot valid for the duration
 * of one pipeline execution.
 */
interface RewardContextBuilderContract
{
    /**
     * @throws \App\RewardEngine\Exceptions\RewardEngineException  If user not found.
     */
    public function build(RewardRequest $request): RewardContext;
}
