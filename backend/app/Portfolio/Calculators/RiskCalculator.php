<?php

declare(strict_types=1);

namespace App\Portfolio\Calculators;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\DTOs\PortfolioRiskResult;
use Illuminate\Support\Collection;

/**
 * Class RiskCalculator
 *
 * Evaluates drawdown, volatility, concentration risks, and risk/health scores.
 */
final class RiskCalculator
{
    /**
     * Calculates risk metrics.
     *
     * @param PortfolioContext $context
     * @param int $netWorthPaise
     * @param int $holdingValuePaise
     * @param float $winRate
     * @param Collection $historicalSnapshots Collection of PortfolioSnapshot
     * @return PortfolioRiskResult
     */
    public function calculate(
        PortfolioContext $context,
        int $netWorthPaise,
        int $holdingValuePaise,
        float $winRate,
        Collection $historicalSnapshots
    ): PortfolioRiskResult {
        // Sort historical snapshots ascending by taken_at to ensure chronological order
        $snapshots = $historicalSnapshots->sortBy(function ($s) {
            return $s->taken_at ? $s->taken_at->timestamp : $s->created_at->timestamp;
        });

        // 1. Calculate Maximum Drawdown
        $maxDrawdownPercent = 0.0;
        if ($snapshots->count() > 0) {
            $peak = 0;
            foreach ($snapshots as $snap) {
                $val = $snap->total_portfolio_value_paise;
                if ($val > $peak) {
                    $peak = $val;
                }
                if ($peak > 0) {
                    $dd = (($peak - $val) / $peak) * 100;
                    if ($dd > $maxDrawdownPercent) {
                        $maxDrawdownPercent = (float) $dd;
                    }
                }
            }
        }

        // 2. Calculate Volatility (Standard Deviation of Daily Returns)
        $volatility = 0.0;
        if ($snapshots->count() >= 3) { // Need at least 3 points to get 2 returns
            $returns = [];
            $prevValue = null;
            foreach ($snapshots as $snap) {
                $val = $snap->total_portfolio_value_paise;
                if ($prevValue !== null && $prevValue > 0) {
                    $returns[] = ($val - $prevValue) / $prevValue;
                }
                $prevValue = $val;
            }

            $count = count($returns);
            if ($count > 1) {
                $mean = array_sum($returns) / $count;
                $sumSquares = 0.0;
                foreach ($returns as $r) {
                    $sumSquares += pow($r - $mean, 2);
                }
                $variance = $sumSquares / ($count - 1);
                $volatility = (float) sqrt($variance);
            }
        }

        // 3. Position and Sector exposures
        $exposurePerStock = [];
        $exposurePerSector = [];
        $largestPosition = null;
        $maxStockVal = 0;

        foreach ($context->holdings as $holding) {
            if ($holding->quantity > 0) {
                $quote = $context->getQuote($holding->symbol);
                $holdingVal = $holding->quantity * ($quote !== null ? $quote->ltp->valuePaise : $holding->current_value_paise);

                $exposurePerStock[$holding->symbol] = ($exposurePerStock[$holding->symbol] ?? 0) + $holdingVal;

                $stockModel = $holding->stock;
                $sectorName = ($stockModel !== null && $stockModel->sector) ? $stockModel->sector : 'Unknown';
                $exposurePerSector[$sectorName] = ($exposurePerSector[$sectorName] ?? 0) + $holdingVal;

                if ($holdingVal > $maxStockVal) {
                    $maxStockVal = $holdingVal;
                    $largestPosition = $holding->symbol;
                }
            }
        }

        // 4. Cash Risk Percent
        $cashRiskPercent = $netWorthPaise > 0 
            ? (float) (($context->cashPaise() * 100) / $netWorthPaise) 
            : 0.0;

        // 5. Calculate Risk Score (0-100)
        // High stock concentration, high volatility, and low cash increase the risk score
        $concentrationScore = $holdingValuePaise > 0 
            ? ($maxStockVal / $holdingValuePaise) * 100 
            : 0.0;
        
        $volRisk = min(100.0, $volatility * 1000); // Scale volatility up for score contribution
        $cashRiskContrib = max(0.0, (50.0 - $cashRiskPercent) * 0.5); // Cash cushion reduces risk score

        $riskScoreVal = ($concentrationScore * 0.4) + ($volRisk * 0.4) + $cashRiskContrib;
        $riskScore = (int) max(0, min(100, round($riskScoreVal)));

        // 6. Calculate Health Score (0-100)
        // Positive win rate, moderate risk, and active diversification improve health score
        $hhi = 0.0;
        foreach ($exposurePerStock as $exp) {
            if ($holdingValuePaise > 0) {
                $share = ($exp / $holdingValuePaise) * 100;
                $hhi += $share * $share;
            }
        }
        $diversificationContribution = $holdingValuePaise > 0 
            ? max(0, min(100, (10000 - $hhi) / 80)) 
            : 100; // 100% cash is fully diversified risk-wise

        $winRateContrib = ($winRate / 100) * 30; // Up to 30 points
        $riskDeduction = $riskScore * 0.4; // Subtract up to 40 points
        $diversificationContrib = $diversificationContribution * 0.3; // Up to 30 points
        
        // Base score of 40
        $healthScoreVal = 40 + $winRateContrib + $diversificationContrib - $riskDeduction;
        $healthScore = (int) max(0, min(100, round($healthScoreVal)));

        return new PortfolioRiskResult(
            maxDrawdownPercent: $maxDrawdownPercent,
            volatility: $volatility,
            largestPosition: $largestPosition,
            exposurePerStock: $exposurePerStock,
            exposurePerSector: $exposurePerSector,
            cashRiskPercent: $cashRiskPercent,
            riskScore: $riskScore,
            healthScore: $healthScore
        );
    }
}
