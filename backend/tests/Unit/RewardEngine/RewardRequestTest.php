<?php
declare(strict_types=1);

namespace Tests\Unit\RewardEngine;

use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardType;
use Tests\TestCase;

/**
 * @group reward-engine
 * @group dto
 */
class RewardRequestTest extends TestCase
{
    /** @test */
    public function make_produces_correct_idempotency_key(): void
    {
        $request = RewardRequest::make(
            userId:     7,
            rewardType: RewardType::Coins,
            source:     RewardSource::Mission,
            sourceId:   '42',
        );

        // Key format: "{source}:{rewardType}:{sourceId}:{userId}"
        $this->assertEquals('mission:coins:42:7', $request->idempotencyKey);
    }

    /** @test */
    public function as_dry_run_returns_copy_with_dry_run_true(): void
    {
        $original = RewardRequest::make(1, RewardType::XP, RewardSource::DailyLogin, 'today');
        $dryRun   = $original->asDryRun();

        $this->assertFalse($original->dryRun);
        $this->assertTrue($dryRun->dryRun);
        $this->assertEquals($original->userId, $dryRun->userId);
        $this->assertEquals($original->idempotencyKey, $dryRun->idempotencyKey);
    }

    /** @test */
    public function meta_returns_correct_value_or_default(): void
    {
        $request = RewardRequest::make(
            userId:     1,
            rewardType: RewardType::InventoryItem,
            source:     RewardSource::Achievement,
            sourceId:   '5',
            metadata:   ['store_item_id' => 99, 'premium_only' => true],
        );

        $this->assertEquals(99, $request->meta('store_item_id'));
        $this->assertTrue($request->meta('premium_only'));
        $this->assertNull($request->meta('nonexistent'));
        $this->assertEquals('default', $request->meta('nonexistent', 'default'));
    }

    /** @test */
    public function admin_source_bypasses_validation(): void
    {
        $this->assertTrue(RewardSource::Admin->bypassesValidation());
        $this->assertFalse(RewardSource::Mission->bypassesValidation());
    }

    /** @test */
    public function referral_source_requires_referral_check(): void
    {
        $this->assertTrue(RewardSource::Referral->requiresReferralCheck());
        $this->assertFalse(RewardSource::Achievement->requiresReferralCheck());
    }
}
