<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paper Trading Tycoon — Feature Flags API Test
 */
final class FeatureFlagsTest extends TestCase
{
    public function test_feature_flags_endpoint_returns_flags(): void
    {
        $response = $this->getJson('/api/v1/feature-flags');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data']);
    }
}
