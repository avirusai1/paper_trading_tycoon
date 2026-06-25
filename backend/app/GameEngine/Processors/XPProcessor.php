<?php
declare(strict_types=1);

namespace App\GameEngine\Processors;

use App\GameEngine\Actions\GrantXPAction;
use App\GameEngine\Contracts\XPProcessorContract;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\XPResult;
use App\GameEngine\Enums\XPSource;
use App\GameEngine\Exceptions\XPException;
use App\GameEngine\Support\DailyCapTracker;

/**
 * Implements XPProcessorContract by delegating to GrantXPAction.
 *
 * The processor layer is the integration point between the pipeline and the
 * action layer. It applies any cross-cutting concerns (logging, metrics) and
 * exposes the contract interface consumed by the pipeline.
 */
final class XPProcessor implements XPProcessorContract
{
    public function __construct(
        private readonly GrantXPAction   $grantXPAction,
        private readonly DailyCapTracker $capTracker,
    ) {}

    public function grant(
        GameContext $context,
        XPSource    $source,
        string      $sourceId,
        ?int        $overrideAmount = null,
    ): XPResult {
        return $this->grantXPAction->execute($context, $source, $sourceId, $overrideAmount);
    }

    public function getDailyTotal(int $userId, XPSource $source): int
    {
        return $this->capTracker->getDailyTotal($userId, $source);
    }
}
