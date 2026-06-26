<?php

declare(strict_types=1);

namespace Tests\Unit\MarketData;

use App\MarketData\Providers\MockProvider;
use App\MarketData\ValueObjects\Ticker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MockProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_mock_provider_fetches_quote(): void
    {
        $provider = new MockProvider;
        $quote = $provider->getQuote(new Ticker('RELIANCE'));

        $this->assertEquals('RELIANCE', $quote->ticker->symbol);
        $this->assertGreaterThan(0, $quote->ltp->valuePaise);
        $this->assertNotNull($quote->marketStatus);
    }

    /** @test */
    public function test_mock_provider_search(): void
    {
        $provider = new MockProvider;
        $results = $provider->searchStocks('RELIANCE');
        $this->assertIsArray($results);
    }
}
