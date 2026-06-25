<?php
declare(strict_types=1);

namespace Tests\Unit\RewardEngine;

use App\GameEngine\Contracts\GameRuleProviderContract;
use App\RewardEngine\Calculators\MultiplierResolver;
use App\RewardEngine\Calculators\XPCalculator;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardType;
use App\RewardEngine\Exceptions\RewardCalculationException;
use Mockery;
use Tests\TestCase;

/**
 * @group reward-engine
 * @group calculators
 */
class XPCalculatorTest extends TestCase
{
    /** @test */
    public function it_returns_base_xp_with_no_multipliers(): void
    {
        $rules = Mockery::mock(GameRuleProviderContract::class);
        $rules->shouldReceive('getInt')->with('rewards.xp.mission', -1)->andReturn(100);

        $resolver = Mockery::mock(MultiplierResolver::class);
        $resolver->shouldReceive('breakdown')->andReturn([]);

        $calculator = new XPCalculator($rules, $resolver);
        $request    = RewardRequest::make(1, RewardType::XP, RewardSource::Mission, '1');
        $context    = Mockery::mock(RewardContext::class);
        $context->shouldReceive('currentXP')->andReturn(0);

        $result = $calculator->calculate($request, $context);

        $this->assertEquals(100, $result->baseXP);
        $this->assertEquals(100, $result->finalXP);
        $this->assertEquals(1.0, $result->totalMultiplier);
    }

    /** @test */
    public function it_applies_multipliers_to_base_xp(): void
    {
        $rules = Mockery::mock(GameRuleProviderContract::class);
        $rules->shouldReceive('getInt')->with('rewards.xp.mission', -1)->andReturn(100);

        $resolver = Mockery::mock(MultiplierResolver::class);
        $resolver->shouldReceive('breakdown')->andReturn(['premium_xp' => 2.0]);

        $calculator = new XPCalculator($rules, $resolver);
        $request    = RewardRequest::make(1, RewardType::XP, RewardSource::Mission, '1');
        $context    = Mockery::mock(RewardContext::class);

        $result = $calculator->calculate($request, $context);

        $this->assertEquals(100, $result->baseXP);
        $this->assertEquals(200, $result->finalXP);
    }

    /** @test */
    public function it_uses_override_amount_when_provided(): void
    {
        $rules    = Mockery::mock(GameRuleProviderContract::class);
        $resolver = Mockery::mock(MultiplierResolver::class);
        $resolver->shouldReceive('breakdown')->andReturn([]);

        $calculator = new XPCalculator($rules, $resolver);
        $request    = new \App\RewardEngine\DTOs\RewardRequest(
            userId:         1,
            rewardType:     RewardType::XP,
            source:         RewardSource::Admin,
            sourceId:       'admin_1',
            idempotencyKey: 'admin:xp:admin_1:1',
            overrideAmount: 500,
        );
        $context = Mockery::mock(RewardContext::class);

        $result = $calculator->calculate($request, $context);

        $this->assertEquals(500, $result->baseXP);
        $this->assertEquals(500, $result->finalXP);
    }

    /** @test */
    public function it_throws_when_rule_key_missing(): void
    {
        $this->expectException(RewardCalculationException::class);

        $rules = Mockery::mock(GameRuleProviderContract::class);
        $rules->shouldReceive('getInt')->with('rewards.xp.mission', -1)->andReturn(-1);

        $resolver = Mockery::mock(MultiplierResolver::class);
        $resolver->shouldReceive('breakdown')->andReturn([]);

        $calculator = new XPCalculator($rules, $resolver);
        $request    = RewardRequest::make(1, RewardType::XP, RewardSource::Mission, '1');
        $context    = Mockery::mock(RewardContext::class);

        $calculator->calculate($request, $context);
    }
}
