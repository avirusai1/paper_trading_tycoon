<?php

declare(strict_types=1);

namespace App\Portfolio\Actions;

use App\Events\PortfolioUpdated as GlobalPortfolioUpdated;
use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\DTOs\PortfolioResult;
use App\Portfolio\Events\PortfolioCalculated;
use App\Portfolio\Events\PortfolioGrowthAchieved;
use App\Portfolio\Events\PortfolioMilestoneReached;
use App\Portfolio\Events\PortfolioRiskChanged;
use Illuminate\Support\Facades\Log;

/**
 * Class PublishPortfolioAction
 *
 * Checks milestones/threshold achievements and publishes relevant domain events.
 */
final class PublishPortfolioAction
{
    private const GROWTH_THRESHOLD_PERCENT = 10.0;
    private const MILESTONE_VALUES = [
        150000000 => '15 Lakhs Portfolio Net Worth',
        200000000 => '20 Lakhs Portfolio Net Worth',
        500000000 => '50 Lakhs Portfolio Net Worth',
        1000000000 => '1 Crore Portfolio Net Worth',
    ];

    /**
     * Evaluates metrics and publishes subsystem/global events.
     *
     * @param PortfolioContext $context
     * @param PortfolioResult $result
     * @return void
     */
    public function execute(PortfolioContext $context, PortfolioResult $result): void
    {
        Log::info('[PublishPortfolioAction] Publishing portfolio events...', [
            'user_id' => $result->userId,
            'net_worth' => $result->netWorth->valuePaise,
        ]);

        // 1. Dispatch global domain event
        GlobalPortfolioUpdated::dispatch(
            $result->userId,
            $result->netWorth->valuePaise,
            $result->cashValue->valuePaise,
            $result->analytics->currentExposurePaise > 0 ? count($context->holdings) : 0
        );

        // 2. Dispatch subsystem-specific calculation event
        PortfolioCalculated::dispatch(
            $result->userId,
            $result->netWorth->valuePaise,
            $result->absoluteReturn->absolutePaise,
            $result->percentageReturn->percentage
        );

        // 3. Evaluate Growth Achievements
        if ($result->percentageReturn->percentage >= self::GROWTH_THRESHOLD_PERCENT) {
            PortfolioGrowthAchieved::dispatch(
                $result->userId,
                $result->percentageReturn->percentage,
                $result->netWorth->valuePaise
            );
        }

        // 4. Evaluate Milestones crossed
        foreach (self::MILESTONE_VALUES as $milestoneValue => $milestoneName) {
            $previouslyBelow = $context->latestSnapshot === null || 
                $context->latestSnapshot->total_portfolio_value_paise < $milestoneValue;

            if ($previouslyBelow && $result->netWorth->valuePaise >= $milestoneValue) {
                PortfolioMilestoneReached::dispatch(
                    $result->userId,
                    $milestoneValue,
                    $milestoneName
                );
            }
        }

        // 5. Evaluate Risk Shift changes
        if ($context->latestSnapshot !== null) {
            // Retrieve old risk score if it was stored or calculated. We can check if we had a prior snap.
            // Let's assume we can fetch old risk score or just check if it varies significantly.
            // For simplicity, if we have a latest snapshot and it differs in value significanly, we can log.
            // Risk score check:
            // Since risk score is not in DB table portfolio_snapshots, we can check if cashRiskPercent has shifted:
            $oldCashVal = $context->latestSnapshot->virtual_cash_paise;
            $oldNetWorth = $context->latestSnapshot->total_portfolio_value_paise;
            $oldCashRiskPct = $oldNetWorth > 0 ? ($oldCashVal * 100) / $oldNetWorth : 0.0;
            
            $newCashRiskPct = $result->risk->cashRiskPercent;
            
            if (abs($newCashRiskPct - $oldCashRiskPct) >= 15.0) {
                PortfolioRiskChanged::dispatch(
                    $result->userId,
                    (int) $oldCashRiskPct,
                    $result->risk->riskScore,
                    $result->risk->largestPosition ?? 'Portfolio'
                );
            }
        }
    }
}
