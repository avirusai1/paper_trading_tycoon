<?php
declare(strict_types=1);

namespace App\RewardEngine\DTOs;

/**
 * Aggregated result of a batch distribute() call.
 *
 * Individual reward results may succeed or fail independently.
 * Callers should check hasFailures() and inspect each result.
 */
final readonly class RewardBatchResult
{
    /**
     * @param  RewardEngineResult[]  $results
     */
    public function __construct(
        public readonly array $results,
        public readonly int   $userId,
        public readonly float $processingTimeMs,
    ) {}

    public function hasFailures(): bool
    {
        foreach ($this->results as $result) {
            if ($result->failed()) {
                return true;
            }
        }
        return false;
    }

    public function successCount(): int
    {
        return count(array_filter($this->results, fn ($r) => $r->succeeded()));
    }

    public function failureCount(): int
    {
        return count(array_filter($this->results, fn ($r) => $r->failed()));
    }

    public function totalXPGranted(): int
    {
        return array_sum(array_map(fn ($r) => $r->totalXPGranted, $this->results));
    }

    public function totalCoinsGranted(): int
    {
        return array_sum(array_map(fn ($r) => $r->totalCoinsGranted, $this->results));
    }
}
