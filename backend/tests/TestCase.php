<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

/**
 * Paper Trading Tycoon — Base TestCase
 *
 * All Laravel tests extend this class.
 * Provides common setup helpers for API testing:
 *   - actingAsUser()    — creates and authenticates a test user via Sanctum
 *   - assertApiSuccess() — asserts the standard API success envelope
 *   - assertApiError()   — asserts the standard API error envelope
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Assert the response follows the success envelope.
     *
     * @param  TestResponse  $response
     */
    protected function assertApiSuccess(mixed $response, int $status = 200): void
    {
        $response->assertStatus($status)
            ->assertJsonStructure(['success', 'data'])
            ->assertJson(['success' => true]);
    }

    /**
     * Assert the response follows the error envelope.
     *
     * @param  TestResponse  $response
     */
    protected function assertApiError(mixed $response, int $status): void
    {
        $response->assertStatus($status)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson(['success' => false]);
    }
}
