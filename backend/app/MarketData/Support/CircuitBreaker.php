<?php

declare(strict_types=1);

namespace App\MarketData\Support;

use Illuminate\Support\Facades\Cache;

final class CircuitBreaker
{
    private const FAILURE_THRESHOLD = 3;
    private const TIMEOUT_SECONDS = 60;

    public function __construct(private string $providerName) {}

    public function isAvailable(): bool
    {
        $state = Cache::get($this->getStateKey(), 'CLOSED');

        if ($state === 'OPEN') {
            $lastFailure = Cache::get($this->getLastFailureKey(), 0);
            if (time() - $lastFailure > self::TIMEOUT_SECONDS) {
                Cache::put($this->getStateKey(), 'HALF-OPEN', self::TIMEOUT_SECONDS);

                return true;
            }

            return false;
        }

        return true;
    }

    public function recordSuccess(): void
    {
        Cache::forget($this->getFailuresKey());
        Cache::put($this->getStateKey(), 'CLOSED', self::TIMEOUT_SECONDS);
        Cache::forget($this->getLastFailureKey());
    }

    public function recordFailure(): void
    {
        $failures = (int) Cache::get($this->getFailuresKey(), 0) + 1;
        Cache::put($this->getFailuresKey(), $failures, self::TIMEOUT_SECONDS * 2);
        Cache::put($this->getLastFailureKey(), time(), self::TIMEOUT_SECONDS * 2);

        if ($failures >= self::FAILURE_THRESHOLD) {
            Cache::put($this->getStateKey(), 'OPEN', self::TIMEOUT_SECONDS * 2);
        }
    }

    private function getFailuresKey(): string
    {
        return "circuit_breaker:{$this->providerName}:failures";
    }

    private function getStateKey(): string
    {
        return "circuit_breaker:{$this->providerName}:state";
    }

    private function getLastFailureKey(): string
    {
        return "circuit_breaker:{$this->providerName}:last_failure";
    }
}
