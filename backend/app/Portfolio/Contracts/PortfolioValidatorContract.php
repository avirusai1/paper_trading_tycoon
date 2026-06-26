<?php

declare(strict_types=1);

namespace App\Portfolio\Contracts;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\Exceptions\PortfolioValidationException;

/**
 * Interface PortfolioValidatorContract
 *
 * Contract for a pipeline validator checking portfolio integrity/state constraints.
 */
interface PortfolioValidatorContract
{
    /**
     * Validates constraints against the current PortfolioContext.
     *
     * @param PortfolioContext $context
     * @throws PortfolioValidationException
     * @return void
     */
    public function validate(PortfolioContext $context): void;
}
