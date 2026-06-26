<?php

declare(strict_types=1);

namespace App\Portfolio\Calculators;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\DTOs\PortfolioAnalyticsResult;
use Carbon\Carbon;

/**
 * Class AnalyticsCalculator
 *
 * Performs trade history analysis and portfolio diversification evaluations.
 */
final class AnalyticsCalculator
{
    /**
     * Calculates trading performance and allocation metrics.
     *
     * @param PortfolioContext $context
     * @param int $netWorthPaise
     * @param int $holdingValuePaise
     * @return PortfolioAnalyticsResult
     */
    public function calculate(
        PortfolioContext $context,
        int $netWorthPaise,
        int $holdingValuePaise
    ): PortfolioAnalyticsResult {
        $trades = $context->trades->sortBy(function ($trade) {
            return $trade->executed_at->timestamp . '_' . $trade->id;
        });

        $totalTrades = $trades->count();
        $winningTrades = 0;
        $losingTrades = 0;

        $largestWinner = null; // ['symbol' => string, 'amount_paise' => int]
        $largestLoser = null;  // ['symbol' => string, 'amount_paise' => int]

        $stockPnl = []; // symbol -> cumulative P&L paise
        $inventory = []; // symbol -> array of ['qty' => int, 'price' => int, 'time' => Carbon]

        $totalHoldingSeconds = 0.0;
        $totalHoldingWeightedQty = 0;

        $sumReturnsPercent = 0.0;
        $totalReturnsWeightedQty = 0;

        foreach ($trades as $trade) {
            $symbol = $trade->symbol;
            $qty = $trade->quantity;
            $price = $trade->price_paise;
            $side = strtolower($trade->side instanceof \BackedEnum ? $trade->side->value : (string)$trade->side);
            $executedAt = $trade->executed_at;

            if ($side === 'buy') {
                if (!isset($inventory[$symbol])) {
                    $inventory[$symbol] = [];
                }
                $inventory[$symbol][] = [
                    'qty' => $qty,
                    'price' => $price,
                    'time' => $executedAt,
                ];
            } elseif ($side === 'sell') {
                $sellQtyRemaining = $qty;
                $sellCostBasis = 0;
                $matchedPortions = [];

                if (isset($inventory[$symbol])) {
                    while ($sellQtyRemaining > 0 && count($inventory[$symbol]) > 0) {
                        $oldestBuy = &$inventory[$symbol][0];
                        $matchQty = min($sellQtyRemaining, $oldestBuy['qty']);

                        $sellCostBasis += $matchQty * $oldestBuy['price'];
                        $sellQtyRemaining -= $matchQty;
                        $oldestBuy['qty'] -= $matchQty;

                        // Calculate holding time
                        $buyTime = Carbon::parse($oldestBuy['time']);
                        $sellTime = Carbon::parse($executedAt);
                        $holdingSeconds = max(0, $sellTime->diffInSeconds($buyTime));

                        $totalHoldingSeconds += $holdingSeconds * $matchQty;
                        $totalHoldingWeightedQty += $matchQty;

                        // Calculate return percent
                        $portionReturnPercent = $oldestBuy['price'] > 0 
                            ? (($price - $oldestBuy['price']) / $oldestBuy['price']) * 100 
                            : 0.0;
                        $sumReturnsPercent += $portionReturnPercent * $matchQty;
                        $totalReturnsWeightedQty += $matchQty;

                        if ($oldestBuy['qty'] === 0) {
                            array_shift($inventory[$symbol]);
                        }
                    }
                }

                // If some sell quantity wasn't matched due to missing buy trade history, assume cost is sell price (no p&l)
                if ($sellQtyRemaining > 0) {
                    $sellCostBasis += $sellQtyRemaining * $price;
                }

                $sellProceeds = $qty * $price;
                $tradeRealizedPnl = $sellProceeds - $sellCostBasis;

                if (!isset($stockPnl[$symbol])) {
                    $stockPnl[$symbol] = 0;
                }
                $stockPnl[$symbol] += $tradeRealizedPnl;

                if ($tradeRealizedPnl > 0) {
                    $winningTrades++;
                    if ($largestWinner === null || $tradeRealizedPnl > $largestWinner['amount_paise']) {
                        $largestWinner = [
                            'symbol' => $symbol,
                            'amount_paise' => $tradeRealizedPnl,
                        ];
                    }
                } elseif ($tradeRealizedPnl < 0) {
                    $losingTrades++;
                    if ($largestLoser === null || $tradeRealizedPnl < $largestLoser['amount_paise']) {
                        // Keep track of largest loss (most negative amount)
                        $largestLoser = [
                            'symbol' => $symbol,
                            'amount_paise' => $tradeRealizedPnl,
                        ];
                    }
                }
            }
        }

        // Determine best and worst stock
        $bestStock = null;
        $worstStock = null;
        if (!empty($stockPnl)) {
            arsort($stockPnl);
            $bestStock = (string) array_key_first($stockPnl);
            $worstStock = (string) array_key_last($stockPnl);
        }

        // Average holding period
        $avgHoldingPeriod = $totalHoldingWeightedQty > 0 
            ? (float) ($totalHoldingSeconds / $totalHoldingWeightedQty) 
            : 0.0;

        // Average return percent
        $avgReturnPercent = $totalReturnsWeightedQty > 0 
            ? (float) ($sumReturnsPercent / $totalReturnsWeightedQty) 
            : 0.0;

        // Win rate calculation
        $totalClosed = $winningTrades + $losingTrades;
        $winRate = $totalClosed > 0 ? (float) (($winningTrades / $totalClosed) * 100) : 0.0;

        // Current Exposure
        $currentExposurePaise = $holdingValuePaise;

        // Allocations & Concentration
        $stockExposure = [];
        $sectorExposure = [];

        foreach ($context->holdings as $holding) {
            if ($holding->quantity > 0) {
                $quote = $context->getQuote($holding->symbol);
                $holdingVal = $holding->quantity * ($quote !== null ? $quote->ltp->valuePaise : $holding->current_value_paise);

                $stockExposure[$holding->symbol] = ($stockExposure[$holding->symbol] ?? 0) + $holdingVal;

                $stockModel = $holding->stock;
                $sectorName = ($stockModel !== null && $stockModel->sector) ? $stockModel->sector : 'Unknown';
                $sectorExposure[$sectorName] = ($sectorExposure[$sectorName] ?? 0) + $holdingVal;
            }
        }

        // Sector Allocation percentages
        $sectorAllocation = [];
        foreach ($sectorExposure as $sector => $exposure) {
            $sectorAllocation[$sector] = $netWorthPaise > 0 
                ? (float) (($exposure * 100) / $netWorthPaise) 
                : 0.0;
        }

        // Diversification Score & Concentration using HHI
        $hhi = 0.0;
        $maxStockExposure = 0;
        foreach ($stockExposure as $exposure) {
            if ($holdingValuePaise > 0) {
                $share = ($exposure / $holdingValuePaise) * 100;
                $hhi += $share * $share;
            }
            $maxStockExposure = max($maxStockExposure, $exposure);
        }

        // If no stock holdings, HHI = 0, diversification score = 100 (no stock risk)
        $diversificationScore = $holdingValuePaise > 0 
            ? (int) max(0, min(100, (10000 - $hhi) / 80)) 
            : 100;

        $portfolioConcentration = $holdingValuePaise > 0 
            ? (float) (($maxStockExposure * 100) / $holdingValuePaise) 
            : 0.0;

        // Cash allocation
        $cashAllocation = $netWorthPaise > 0 
            ? (float) (($context->cashPaise() * 100) / $netWorthPaise) 
            : 0.0;

        return new PortfolioAnalyticsResult(
            totalTrades: $totalTrades,
            winningTrades: $winningTrades,
            losingTrades: $losingTrades,
            winRate: $winRate,
            largestWinner: $largestWinner,
            largestLoser: $largestLoser,
            bestStock: $bestStock,
            worstStock: $worstStock,
            averageHoldingPeriodSeconds: $avgHoldingPeriod,
            averageReturnPercent: $avgReturnPercent,
            currentExposurePaise: $currentExposurePaise,
            sectorAllocation: $sectorAllocation,
            diversificationScore: $diversificationScore,
            portfolioConcentration: $portfolioConcentration,
            cashAllocation: $cashAllocation
        );
    }
}
