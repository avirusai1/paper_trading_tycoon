<?php
declare(strict_types=1);

namespace App\GameEngine\Processors;

use App\GameEngine\Actions\GrantCareerProgressAction;
use App\GameEngine\Contracts\CareerProcessorContract;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\CareerResult;

/**
 * Implements CareerProcessorContract by delegating to GrantCareerProgressAction.
 */
final class CareerProcessor implements CareerProcessorContract
{
    public function __construct(
        private readonly GrantCareerProgressAction $careerAction,
    ) {}

    public function evaluate(GameContext $context): CareerResult
    {
        return $this->careerAction->execute($context);
    }
}
