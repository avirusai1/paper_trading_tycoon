<?php

declare(strict_types=1);

namespace App\Portfolio\Pipelines;

use App\Portfolio\Actions\LoadPortfolioContextAction;
use App\Portfolio\Actions\CalculatePortfolioAction;
use App\Portfolio\Actions\CalculateAnalyticsAction;
use App\Portfolio\Actions\CalculateRiskAction;
use App\Portfolio\Actions\GenerateSnapshotAction;
use App\Portfolio\Actions\PublishPortfolioAction;
use App\Portfolio\Contracts\PortfolioValidatorContract;
use App\Portfolio\DTOs\PortfolioResult;
use App\Portfolio\Exceptions\PortfolioException;
use Illuminate\Support\Facades\Log;

/**
 * Class RefreshPortfolioPipeline
 *
 * Pipelines portfolio refreshes: context hydration -> validation -> calculations -> snapshotting -> event dispatching.
 */
final readonly class RefreshPortfolioPipeline
{
    /**
     * RefreshPortfolioPipeline constructor.
     *
     * @param LoadPortfolioContextAction $loadContextAction
     * @param CalculatePortfolioAction $calculatePortfolioAction
     * @param CalculateAnalyticsAction $calculateAnalyticsAction
     * @param CalculateRiskAction $calculateRiskAction
     * @param GenerateSnapshotAction $generateSnapshotAction
     * @param PublishPortfolioAction $publishPortfolioAction
     * @param PortfolioValidatorContract[] $validators
     */
    public function __construct(
        private LoadPortfolioContextAction $loadContextAction,
        private CalculatePortfolioAction $calculatePortfolioAction,
        private CalculateAnalyticsAction $calculateAnalyticsAction,
        private CalculateRiskAction $calculateRiskAction,
        private GenerateSnapshotAction $generateSnapshotAction,
        private PublishPortfolioAction $publishPortfolioAction,
        private array $validators
    ) {}

    /**
     * Executes the refresh pipeline for a user ID.
     *
     * @param int $userId
     * @param string $snapshotType ('daily', 'hourly', 'manual')
     * @return PortfolioResult
     * @throws PortfolioException
     */
    public function execute(int $userId, string $snapshotType = 'manual'): PortfolioResult
    {
        $startTime = microtime(true);

        Log::info('[RefreshPortfolioPipeline] Refreshing portfolio for user', ['user_id' => $userId]);

        // 1. Build immutable context
        $context = $this->loadContextAction->execute($userId);

        // 2. Validate
        foreach ($this->validators as $validator) {
            $validator->validate($context);
        }

        // 3. Calculate Core Valuations and Returns
        $values = $this->calculatePortfolioAction->execute($context);

        // 4. Calculate Analytics
        $analytics = $this->calculateAnalyticsAction->execute(
            $context,
            $values['netWorth']->valuePaise,
            $values['holdingValue']->valuePaise
        );

        // 5. Calculate Risk Metrics
        $risk = $this->calculateRiskAction->execute(
            $context,
            $values['netWorth']->valuePaise,
            $values['holdingValue']->valuePaise,
            $analytics->winRate
        );

        // 6. Generate Snapshot
        $this->generateSnapshotAction->execute(
            $context,
            $values['cashValue']->valuePaise,
            $values['holdingValue']->valuePaise,
            $values['netWorth']->valuePaise,
            $values['absoluteReturn']->absolutePaise,
            (float) $values['absoluteReturn']->percentage,
            $snapshotType
        );

        $elapsed = round((microtime(true) - $startTime) * 1000, 2);

        // Construct master result DTO
        $result = new PortfolioResult(
            userId: $userId,
            netWorth: $values['netWorth'],
            cashValue: $values['cashValue'],
            holdingValue: $values['holdingValue'],
            absoluteReturn: $values['absoluteReturn'],
            percentageReturn: $values['percentageReturn'],
            todayProfitLoss: $values['todayProfitLoss'],
            analytics: $analytics,
            risk: $risk,
            elapsedTimeMs: $elapsed
        );

        // 7. Publish events
        $this->publishPortfolioAction->execute($context, $result);

        return $result;
    }
}
