<?php

declare(strict_types=1);

namespace Tests\Unit\MarketData;

use App\MarketData\Support\CircuitBreaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CircuitBreakerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::clear();
    }

    /** @test */
    public function test_circuit_breaker_flow(): void
    {
        $cb = new CircuitBreaker('TestProvider');

        $this->assertTrue($cb->isAvailable());

        $cb->recordFailure();
        $this->assertTrue($cb->isAvailable());

        $cb->recordFailure();
        $this->assertTrue($cb->isAvailable());

        $cb->recordFailure();
        $this->assertFalse($cb->isAvailable());

        $cb->recordSuccess();
        $this->assertTrue($cb->isAvailable());
    }
}
