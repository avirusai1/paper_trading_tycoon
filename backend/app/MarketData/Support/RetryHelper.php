<?php

declare(strict_types=1);

namespace App\MarketData\Support;

use Exception;

final class RetryHelper
{
    /**
     * Executes the given callback, retrying on exceptions.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @param  int  $initialDelayMs  Initial delay in milliseconds
     * @param  float  $multiplier  Exponential multiplier
     * @return T
     *
     * @throws Exception
     */
    public static function retry(
        callable $callback,
        int $maxAttempts = 3,
        int $initialDelayMs = 100,
        float $multiplier = 2.0
    ) {
        $attempts = 0;
        $delay = $initialDelayMs;

        while (true) {
            try {
                $attempts++;

                return $callback();
            } catch (Exception $e) {
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }

                usleep($delay * 1000);
                $delay = (int) ($delay * $multiplier);
            }
        }
    }
}
