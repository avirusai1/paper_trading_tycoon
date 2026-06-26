<?php

declare(strict_types=1);

namespace App\RewardEngine\Validators;

use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\RewardValidatorContract;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Exceptions\RewardValidationException;

/**
 * Rejects season-based rewards when no active season exists.
 *
 * Only runs for RewardSource::Season sources.
 */
final class SeasonValidityValidator implements RewardValidatorContract
{
    public function validate(RewardRequest $request, RewardContext $context): void
    {
        if ($request->source !== RewardSource::Season) {
            return;
        }

        if (! $context->hasActiveSeason()) {
            throw RewardValidationException::invalidSeason();
        }

        // Verify the season ID in metadata matches the active season, if provided.
        $expectedSeasonId = $request->meta('season_id');
        if ($expectedSeasonId !== null
            && (int) $expectedSeasonId !== $context->activeSeason?->id) {
            throw RewardValidationException::invalidSeason();
        }
    }
}
