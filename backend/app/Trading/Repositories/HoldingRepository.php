<?php

declare(strict_types=1);

namespace App\Trading\Repositories;

use App\Enums\OrderSide;
use App\Models\Holding;
use App\Trading\Contracts\HoldingRepositoryContract;
use InvalidArgumentException;

/**
 * Eloquent implementation of HoldingRepositoryContract.
 * Calculates average buy price and total invested using weighted average cost basis.
 */
final class HoldingRepository implements HoldingRepositoryContract
{
    public function updateHolding(
        int $userId,
        int $stockId,
        string $symbol,
        int $quantityChange,
        int $pricePaise,
        OrderSide $side
    ): Holding {
        /** @var Holding|null $holding */
        $holding = Holding::query()
            ->where('user_id', $userId)
            ->where('stock_id', $stockId)
            ->first();

        if ($side === OrderSide::Buy) {
            if ($holding !== null && $holding->quantity > 0) {
                $oldQty = $holding->quantity;
                $oldAvg = $holding->average_buy_price_paise;

                $newQty = $oldQty + $quantityChange;
                // Weighted average formula
                $newAvg = (int) div_safe(
                    (string) (($oldQty * $oldAvg) + ($quantityChange * $pricePaise)),
                    (string) $newQty
                );
                $newInvested = $newQty * $newAvg;
            } else {
                $newQty = $quantityChange;
                $newAvg = $pricePaise;
                $newInvested = $quantityChange * $pricePaise;
            }
        } else {
            // Sell
            if ($holding === null || $holding->quantity < $quantityChange) {
                throw new InvalidArgumentException('Insufficient holding quantity to execute sell trade.');
            }

            $oldQty = $holding->quantity;
            $oldAvg = $holding->average_buy_price_paise;

            $newQty = $oldQty - $quantityChange;
            $newAvg = $newQty > 0 ? $oldAvg : 0;
            $newInvested = $newQty * $newAvg;
        }

        $currentValue = $newQty * $pricePaise;
        $unrealisedPnl = $currentValue - $newInvested;

        if ($holding !== null) {
            $holding->update([
                'quantity' => $newQty,
                'average_buy_price_paise' => $newAvg,
                'total_invested_paise' => $newInvested,
                'current_value_paise' => $currentValue,
                'unrealised_pnl_paise' => $unrealisedPnl,
            ]);

            return $holding;
        }

        return Holding::query()->create([
            'user_id' => $userId,
            'stock_id' => $stockId,
            'symbol' => $symbol,
            'quantity' => $newQty,
            'average_buy_price_paise' => $newAvg,
            'total_invested_paise' => $newInvested,
            'current_value_paise' => $currentValue,
            'unrealised_pnl_paise' => $unrealisedPnl,
        ]);
    }
}

/**
 * Helper function for safe division using bcmath to avoid floats.
 */
function div_safe(string $num, string $denom): string
{
    if ($denom === '0') {
        return '0';
    }

    return bcdiv($num, $denom, 0);
}
