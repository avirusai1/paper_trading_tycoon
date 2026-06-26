<?php

declare(strict_types=1);

namespace Tests\Unit\GameEngine;

use App\GameEngine\Contexts\GameContext;
use App\GameEngine\Exceptions\GameEngineException;
use App\GameEngine\Factories\GameContextBuilder;
use App\Models\FeatureFlag;
use App\Models\League;
use App\Models\Season;
use App\Models\User;
use App\Models\UserLeague;
use App\Models\UserLevel;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration-style unit tests for GameContextBuilder.
 *
 * Uses RefreshDatabase to exercise real DB queries. Stubs only external
 * services that aren't relevant (none for the builder).
 *
 * Coverage targets:
 * - build() returns a valid GameContext for a properly seeded user
 * - GameEngineException thrown when user not found
 * - GameEngineException thrown when wallet or user_level records missing
 * - activeSeason is null when no active season exists
 * - currentLeague is populated when user is enrolled in active season
 * - activeMissions contains only non-expired, assigned missions
 * - featureFlags map contains correct boolean values
 */
final class GameContextBuilderTest extends TestCase
{
    use RefreshDatabase;

    private GameContextBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GameContextBuilder;
    }

    /** @test */
    public function it_builds_context_for_valid_user(): void
    {
        $user = User::factory()->create(['status' => 'active', 'is_premium' => false]);
        Wallet::factory()->create(['user_id' => $user->id]);
        UserLevel::factory()->create(['user_id' => $user->id]);

        $context = $this->builder->build($user->id);

        $this->assertInstanceOf(GameContext::class, $context);
        $this->assertSame($user->id, $context->userId());
    }

    /** @test */
    public function it_throws_when_user_not_found(): void
    {
        $this->expectException(GameEngineException::class);

        $this->builder->build(99999);
    }

    /** @test */
    public function it_throws_when_wallet_missing(): void
    {
        $user = User::factory()->create();
        UserLevel::factory()->create(['user_id' => $user->id]);
        // Intentionally NO Wallet

        $this->expectException(GameEngineException::class);

        $this->builder->build($user->id);
    }

    /** @test */
    public function active_season_is_null_when_no_active_season(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->create(['user_id' => $user->id]);
        UserLevel::factory()->create(['user_id' => $user->id]);

        $context = $this->builder->build($user->id);

        $this->assertNull($context->activeSeason);
        $this->assertNull($context->currentLeague);
    }

    /** @test */
    public function it_loads_active_season_and_league(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        Wallet::factory()->create(['user_id' => $user->id]);
        UserLevel::factory()->create(['user_id' => $user->id]);

        $season = Season::factory()->create(['status' => 'active']);
        $league = League::factory()->create(['rank' => 1]);
        UserLeague::factory()->create([
            'user_id' => $user->id,
            'season_id' => $season->id,
            'league_id' => $league->id,
        ]);

        $context = $this->builder->build($user->id);

        $this->assertNotNull($context->activeSeason);
        $this->assertSame($season->id, $context->activeSeason->id);
        $this->assertNotNull($context->currentLeague);
    }

    /** @test */
    public function feature_flags_are_resolved(): void
    {
        $user = User::factory()->create(['status' => 'active', 'is_premium' => false]);
        Wallet::factory()->create(['user_id' => $user->id]);
        UserLevel::factory()->create(['user_id' => $user->id]);

        FeatureFlag::factory()->create([
            'key' => 'new_missions',
            'is_enabled' => true,
            'rollout_percentage' => 100,
            'premium_only' => false,
            'allowed_user_ids' => null,
        ]);

        $context = $this->builder->build($user->id);

        $this->assertArrayHasKey('new_missions', $context->featureFlags);
        $this->assertTrue($context->featureFlags['new_missions']);
    }
}
