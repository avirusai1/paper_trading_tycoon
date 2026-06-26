<?php

declare(strict_types=1);

namespace App\Portfolio\Actions;

use App\Portfolio\Contexts\PortfolioContext;
use App\Portfolio\ValueObjects\Allocation;

/**
 * Class CalculateAllocationAction
 *
 * Computes individual stock and sector allocations for the portfolio.
 */
final class CalculateAllocationAction
{
    /**
     * Calculates allocations.
     *
     * @param PortfolioContext $context
     * @param int $netWorthPaise
     * @return array{stocks: array<int, Allocation>, sectors: array<int, Allocation>, cash: Allocation}
     */
    public function execute(PortfolioContext $context, int $netWorthPaise): array
    {
        $cashPaise = $context->cashPaise();
        $cashPct = $netWorthPaise > 0 ? (float) (($cashPaise * 100) / $netWorthPaise) : 0.0;
        $cashAllocation = new Allocation('Cash', $cashPaise, $cashPct);

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

        $stockAllocations = [];
        foreach ($stockExposure as $symbol => $val) {
            $pct = $netWorthPaise > 0 ? (float) (($val * 100) / $netWorthPaise) : 0.0;
            $stockAllocations[] = new Allocation($symbol, $val, $pct);
        }

        $sectorAllocations = [];
        foreach ($sectorExposure as $sector => $val) {
            $pct = $netWorthPaise > 0 ? (float) (($val * 100) / $netWorthPaise) : 0.0;
            $sectorAllocations[] = new Allocation($sector, $val, $pct);
        }

        // Sort allocations by percentage descending
        usort($stockAllocations, fn($a, $b) => $b->percentage <=> $a->percentage);
        usort($sectorAllocations, fn($a, $b) => $b->percentage <=> $a->percentage);

        return [
            'stocks' => $stockAllocations,
            'sectors' => $sectorAllocations,
            'cash' => $cashAllocation,
        ];
    }
}
