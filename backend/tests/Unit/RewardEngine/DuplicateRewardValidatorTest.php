<?php
declare(strict_types=1);

namespace Tests\Unit\RewardEngine;

use App\Models\RewardHistory;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardType;
use App\RewardEngine\Enums\ValidationFailureReason;
use App\RewardEngine\Exceptions\RewardValidationException;
use App\RewardEngine\Validators\DuplicateRewardValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group reward-engine
 * @group validators
 */
class DuplicateRewardValidatorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_passes_when_no_history_record_exists(): void
    {
        $request   = $this->makeRequest();
        $context   = \Mockery::mock(RewardContext::class);
        $validator = new DuplicateRewardValidator();

        // No history record — should not throw
        $validator->validate($request, $context);

        $this->assertTrue(true); // Reached here = pass
    }

    /** @test */
    public function it_throws_when_duplicate_history_record_exists(): void
    {
        $this->expectException(RewardValidationException::class);

        $request = $this->makeRequest();

        RewardHistory::factory()->create([
            'user_id'     => $request->userId,
            'source_type' => $request->rewardType->value,
            'source_id'   => $request->idempotencyKey,
        ]);

        $context   = \Mockery::mock(RewardContext::class);
        $validator = new DuplicateRewardValidator();

        $validator->validate($request, $context);
    }

    /** @test */
    public function admin_source_bypasses_duplicate_check(): void
    {
        $request = RewardRequest::make(
            userId:     1,
            rewardType: RewardType::AdminReward,
            source:     RewardSource::Admin,
            sourceId:   'admin_grant_1',
        );

        // Even with a history record, admin bypasses
        RewardHistory::factory()->create([
            'user_id'     => $request->userId,
            'source_type' => $request->rewardType->value,
            'source_id'   => $request->idempotencyKey,
        ]);

        $context   = \Mockery::mock(RewardContext::class);
        $validator = new DuplicateRewardValidator();

        $validator->validate($request, $context); // Should not throw

        $this->assertTrue(true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeRequest(): RewardRequest
    {
        return RewardRequest::make(
            userId:     1,
            rewardType: RewardType::Coins,
            source:     RewardSource::Mission,
            sourceId:   '42',
        );
    }
}
