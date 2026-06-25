<?php
declare(strict_types=1);

namespace Tests\Unit\RewardEngine;

use App\RewardEngine\Contracts\RewardStrategyContract;
use App\RewardEngine\Enums\RewardType;
use App\RewardEngine\Exceptions\RewardEngineException;
use App\RewardEngine\Support\RewardStrategyRegistry;
use Mockery;
use Tests\TestCase;

/**
 * @group reward-engine
 * @group registry
 */
class RewardStrategyRegistryTest extends TestCase
{
    /** @test */
    public function it_returns_correct_strategy_for_registered_type(): void
    {
        $strategy = Mockery::mock(RewardStrategyContract::class);
        $strategy->shouldReceive('handles')->andReturn(RewardType::Coins);

        $registry = new RewardStrategyRegistry([$strategy]);

        $this->assertSame($strategy, $registry->get(RewardType::Coins));
    }

    /** @test */
    public function it_throws_for_unregistered_type(): void
    {
        $this->expectException(RewardEngineException::class);

        $registry = new RewardStrategyRegistry([]);
        $registry->get(RewardType::XP);
    }

    /** @test */
    public function has_returns_false_for_unregistered_type(): void
    {
        $registry = new RewardStrategyRegistry([]);
        $this->assertFalse($registry->has(RewardType::Badge));
    }

    /** @test */
    public function has_returns_true_for_registered_type(): void
    {
        $strategy = Mockery::mock(RewardStrategyContract::class);
        $strategy->shouldReceive('handles')->andReturn(RewardType::Badge);

        $registry = new RewardStrategyRegistry([$strategy]);

        $this->assertTrue($registry->has(RewardType::Badge));
    }

    /** @test */
    public function register_adds_strategy_at_runtime(): void
    {
        $registry = new RewardStrategyRegistry([]);

        $strategy = Mockery::mock(RewardStrategyContract::class);
        $strategy->shouldReceive('handles')->andReturn(RewardType::XP);

        $registry->register($strategy);

        $this->assertTrue($registry->has(RewardType::XP));
    }
}
