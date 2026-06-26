<?php

declare(strict_types=1);

namespace Tests\Unit\GameEngine;

use App\GameEngine\Exceptions\GameRuleNotFoundException;
use App\GameEngine\Rules\GameRuleService;
use App\Models\GameRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Unit tests for GameRuleService.
 *
 * Coverage targets:
 * - getInt() returns typed integer value
 * - getFloat() returns typed float value
 * - getBool() returns typed boolean value
 * - getString() returns typed string value
 * - getGroup() returns keyed map for a group
 * - Default values are used when key not found
 * - GameRuleNotFoundException is thrown when key missing and no default
 * - flush() clears the cache (does not throw without tags support)
 * - Cache hit is used on second call (no extra DB queries)
 */
final class GameRuleServiceTest extends TestCase
{
    use RefreshDatabase;

    private GameRuleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = new GameRuleService;
    }

    // ── getInt ────────────────────────────────────────────────────────────────

    /** @test */
    public function it_returns_integer_value_for_existing_key(): void
    {
        GameRule::factory()->create([
            'key' => 'xp.trade_buy',
            'value' => '10',
            'value_type' => 'integer',
            'group' => 'xp',
        ]);

        $result = $this->service->getInt('xp.trade_buy');

        $this->assertSame(10, $result);
    }

    /** @test */
    public function it_returns_default_when_int_key_not_found(): void
    {
        $result = $this->service->getInt('nonexistent.key', 99);

        $this->assertSame(99, $result);
    }

    /** @test */
    public function it_throws_when_int_key_not_found_and_no_default(): void
    {
        $this->expectException(GameRuleNotFoundException::class);

        $this->service->getInt('nonexistent.key');
    }

    // ── getFloat ──────────────────────────────────────────────────────────────

    /** @test */
    public function it_returns_float_value_for_existing_key(): void
    {
        GameRule::factory()->create([
            'key' => 'xp.premium_multiplier',
            'value' => '1.5',
            'value_type' => 'float',
            'group' => 'xp',
        ]);

        $result = $this->service->getFloat('xp.premium_multiplier');

        $this->assertEqualsWithDelta(1.5, $result, 0.001);
    }

    /** @test */
    public function it_returns_default_float_when_key_not_found(): void
    {
        $result = $this->service->getFloat('nonexistent.key', 2.0);

        $this->assertEqualsWithDelta(2.0, $result, 0.001);
    }

    // ── getBool ───────────────────────────────────────────────────────────────

    /** @test */
    public function it_returns_true_for_boolean_rule(): void
    {
        GameRule::factory()->create([
            'key' => 'features.xp_enabled',
            'value' => 'true',
            'value_type' => 'boolean',
            'group' => 'features',
        ]);

        $result = $this->service->getBool('features.xp_enabled');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_for_false_boolean_rule(): void
    {
        GameRule::factory()->create([
            'key' => 'features.coins_disabled',
            'value' => 'false',
            'value_type' => 'boolean',
            'group' => 'features',
        ]);

        $result = $this->service->getBool('features.coins_disabled');

        $this->assertFalse($result);
    }

    // ── getGroup ──────────────────────────────────────────────────────────────

    /** @test */
    public function it_returns_all_rules_for_a_group(): void
    {
        GameRule::factory()->create(['key' => 'xp.trade_buy',  'value' => '10', 'value_type' => 'integer', 'group' => 'xp']);
        GameRule::factory()->create(['key' => 'xp.trade_sell', 'value' => '10', 'value_type' => 'integer', 'group' => 'xp']);

        $group = $this->service->getGroup('xp');

        $this->assertArrayHasKey('trade_buy', $group);
        $this->assertArrayHasKey('trade_sell', $group);
        $this->assertSame(10, $group['trade_buy']);
    }

    // ── flush ─────────────────────────────────────────────────────────────────

    /** @test */
    public function flush_does_not_throw(): void
    {
        $this->service->flush();

        $this->assertTrue(true); // Assert no exception
    }

    /** @test */
    public function it_uses_cache_on_second_call(): void
    {
        GameRule::factory()->create([
            'key' => 'xp.daily_login',
            'value' => '25',
            'value_type' => 'integer',
            'group' => 'xp',
        ]);

        // First call populates cache
        $first = $this->service->getInt('xp.daily_login');
        // Delete the DB record to prove cache is used
        GameRule::where('key', 'xp.daily_login')->delete();
        // Second call should still return cached value
        $second = $this->service->getInt('xp.daily_login');

        $this->assertSame($first, $second);
        $this->assertSame(25, $second);
    }
}
