<?php
declare(strict_types=1);

namespace Tests\Unit\RewardEngine;

use App\GameEngine\Contracts\GameRuleProviderContract;
use App\RewardEngine\Calculators\MultiplierResolver;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\MultiplierType;
use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardType;
use Mockery;
use Tests\TestCase;

/**
 * @group reward-engine
 * @group calculators
 */
class MultiplierResolverTest extends TestCase
{
    /** @test */
    public function it_returns_1_when_no_multipliers_apply(): void
    {
        $rules    = Mockery::mock(GameRuleProviderContract::class);
        $resolver = new MultiplierResolver($rules);

        $context = Mockery::mock(RewardContext::class);
        $context->shouldReceive('isPremium')->andReturn(false);
        $context->allowMockingNonExistentMethods(true);

        // When no premium, no weekend, and no season bonus active, resolve returns 1.0
        $result = $resolver->resolve(MultiplierType::XP, $this->makeContext(isPremium: false, isWeekend: false));

        $this->assertEquals(1.0, $result);
    }

    /** @test */
    public function it_stacks_premium_and_weekend_multipliers(): void
    {
        $rules = Mockery::mock(GameRuleProviderContract::class);
        $rules->shouldReceive('getFloat')
            ->with(MultiplierType::PremiumXP->ruleKey(), 1.0)
            ->andReturn(2.0);
        $rules->shouldReceive('getFloat')
            ->with(MultiplierType::WeekendXP->ruleKey(), 1.0)
            ->andReturn(1.5);

        $resolver = new MultiplierResolver($rules);
        $context  = $this->makeContext(isPremium: true, isWeekend: true);

        $result = $resolver->resolve(MultiplierType::XP, $context);

        $this->assertEquals(3.0, $result); // 2.0 * 1.5 = 3.0
    }

    /** @test */
    public function it_never_returns_below_1(): void
    {
        $rules = Mockery::mock(GameRuleProviderContract::class);
        // Even if rules return 0, multiplier floors at 1.0
        $rules->shouldReceive('getFloat')->andReturn(0.0);

        $resolver = new MultiplierResolver($rules);
        $context  = $this->makeContext(isPremium: true, isWeekend: false);

        $result = $resolver->resolve(MultiplierType::XP, $context);

        $this->assertGreaterThanOrEqual(1.0, $result);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeContext(bool $isPremium, bool $isWeekend): RewardContext
    {
        $context                = Mockery::mock(RewardContext::class);
        $context->isPremium     = $isPremium;
        $context->isWeekend     = $isWeekend;
        $context->multipliers   = [];

        $context->shouldReceive('hasActiveSeason')->andReturn(false);
        $context->shouldReceive('getItemEffectValue')->andReturn(null);

        return $context;
    }
}
