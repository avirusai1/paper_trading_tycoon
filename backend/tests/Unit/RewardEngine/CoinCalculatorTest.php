<?php

declare(strict_types=1);

namespace Tests\Unit\RewardEngine;

use App\GameEngine\Contracts\GameRuleProviderContract;
use App\RewardEngine\Calculators\CoinCalculator;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\Contracts\MultiplierResolverContract;
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
class CoinCalculatorTest extends TestCase
{
    /** @test */
    public function it_calculates_final_coins_with_multiplier(): void
    {
        $rules = Mockery::mock(GameRuleProviderContract::class);
        $rules->shouldReceive('getInt')->with('rewards.coins.mission', -1)->andReturn(10000); // 100 rupees

        $resolver = Mockery::mock(MultiplierResolverContract::class);
        $resolver->shouldReceive('breakdown')->andReturn(['weekend_coins' => 1.5]);

        $calculator = new CoinCalculator($rules, $resolver);
        $request = RewardRequest::make(1, RewardType::Coins, RewardSource::Mission, '1');
        $context = Mockery::mock(RewardContext::class);

        $result = $calculator->calculate($request, $context);

        $this->assertEquals(10000, $result->baseCoins);
        $this->assertEquals(15000, $result->finalCoins); // 10000 * 1.5
    }

    /** @test */
    public function it_uses_bcmath_for_large_paise_amounts(): void
    {
        $rules = Mockery::mock(GameRuleProviderContract::class);
        // Large paise value: ₹10,000 = 1,000,000 paise
        $rules->shouldReceive('getInt')->with('rewards.coins.season', -1)->andReturn(1_000_000);

        $resolver = Mockery::mock(MultiplierResolverContract::class);
        $resolver->shouldReceive('breakdown')->andReturn(['season_bonus' => 1.25]);

        $calculator = new CoinCalculator($rules, $resolver);
        $request = RewardRequest::make(1, RewardType::Coins, RewardSource::Season, '1');
        $context = Mockery::mock(RewardContext::class);

        $result = $calculator->calculate($request, $context);

        $this->assertEquals(1_250_000, $result->finalCoins);
    }

    /** @test */
    public function it_throws_when_rule_key_missing(): void
    {
        $this->expectException(RewardCalculationException::class);

        $rules = Mockery::mock(GameRuleProviderContract::class);
        $rules->shouldReceive('getInt')->andReturn(-1);

        $resolver = Mockery::mock(MultiplierResolverContract::class);
        $resolver->shouldReceive('breakdown')->andReturn([]);

        $calculator = new CoinCalculator($rules, $resolver);
        $request = RewardRequest::make(1, RewardType::Coins, RewardSource::Mission, '1');
        $context = Mockery::mock(RewardContext::class);

        $calculator->calculate($request, $context);
    }
}
