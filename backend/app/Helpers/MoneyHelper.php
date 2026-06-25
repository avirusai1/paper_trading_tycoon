<?php

declare(strict_types=1);

namespace App\Helpers;

use InvalidArgumentException;

/**
 * Paper Trading Tycoon — Monetary Value Helper
 *
 * All monetary values in Paper Trading Tycoon are stored and transmitted
 * as paise integers to eliminate floating-point precision errors.
 * See ADR-004 for the full decision rationale.
 *
 * ₹1 = 100 paise.
 * ₹10,00,000 = 1,000,000,00 paise = 100,000,000 (int).
 *
 * All P&L calculations use bcmath functions to guarantee precision
 * regardless of server PHP float configuration.
 */
final class MoneyHelper
{
    private const PAISE_PER_RUPEE = 100;
    private const BCMATH_SCALE = 0; // Paise are integers; no decimal scale needed.

    /**
     * Convert rupees (float/string) to paise (int).
     * Use when accepting display values from API requests.
     */
    public static function rupeesToPaise(float|string $rupees): int
    {
        // Use bcmath to avoid float multiplication precision loss.
        $result = bcmul((string) $rupees, (string) self::PAISE_PER_RUPEE, self::BCMATH_SCALE);

        return (int) $result;
    }

    /**
     * Convert paise (int) to rupees (string) for display / API responses.
     * Returns a string to preserve decimal precision.
     */
    public static function paiseToRupees(int $paise): string
    {
        return bcdiv((string) $paise, (string) self::PAISE_PER_RUPEE, 2);
    }

    /**
     * Add two paise amounts safely.
     */
    public static function add(int $a, int $b): int
    {
        return (int) bcadd((string) $a, (string) $b, self::BCMATH_SCALE);
    }

    /**
     * Subtract $b from $a in paise.
     */
    public static function subtract(int $a, int $b): int
    {
        return (int) bcsub((string) $a, (string) $b, self::BCMATH_SCALE);
    }

    /**
     * Multiply paise amount by a quantity (integer).
     */
    public static function multiply(int $paise, int $quantity): int
    {
        return (int) bcmul((string) $paise, (string) $quantity, self::BCMATH_SCALE);
    }

    /**
     * Calculate P&L percentage as a string (2 decimal places).
     * Returns 0.00 if cost basis is zero to avoid division by zero.
     */
    public static function plPercentage(int $currentValuePaise, int $costBasisPaise): string
    {
        if ($costBasisPaise === 0) {
            return '0.00';
        }

        $diff = bcsub((string) $currentValuePaise, (string) $costBasisPaise, 0);

        return bcdiv(bcmul($diff, '100', 2), (string) $costBasisPaise, 2);
    }

    /**
     * Validate that a paise amount is positive.
     *
     * @throws InvalidArgumentException
     */
    public static function assertPositive(int $paise, string $field = 'amount'): void
    {
        if ($paise <= 0) {
            throw new InvalidArgumentException("$field must be a positive paise amount. Got: $paise");
        }
    }
}
