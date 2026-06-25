<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Paper Trading Tycoon — Health Check Test
 *
 * Verifies the /api/health endpoint returns a well-formed response.
 * This is the Milestone 1 exit criterion (staging deployment check).
 */
final class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'status',
                    'checks' => ['api', 'database'],
                    'timestamp',
                ],
            ])
            ->assertJson(['success' => true]);
    }

    public function test_health_data_field_is_healthy(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertJsonPath('data.status', 'healthy');
    }
}
