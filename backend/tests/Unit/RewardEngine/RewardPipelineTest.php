<?php
declare(strict_types=1);

namespace Tests\Unit\RewardEngine;

use App\RewardEngine\Contracts\RewardValidatorContract;
use App\RewardEngine\Contexts\RewardContext;
use App\RewardEngine\DTOs\CalculatedReward;
use App\RewardEngine\DTOs\DistributionResult;
use App\RewardEngine\DTOs\RewardRequest;
use App\RewardEngine\Enums\RewardSource;
use App\RewardEngine\Enums\RewardStatus;
use App\RewardEngine\Enums\RewardType;
use App\RewardEngine\Actions\DistributeRewardAction;
use App\RewardEngine\Actions\RecordRewardHistoryAction;
use App\RewardEngine\Actions\RollbackRewardAction;
use App\RewardEngine\Pipelines\RewardPipeline;
use App\RewardEngine\Exceptions\RewardValidationException;
use App\RewardEngine\Enums\ValidationFailureReason;
use Mockery;
use Tests\TestCase;

/**
 * Unit tests for RewardPipeline orchestration logic.
 *
 * @group reward-engine
 * @group pipeline
 */
class RewardPipelineTest extends TestCase
{
    private RewardPipeline $pipeline;
    private DistributeRewardAction $distributeAction;
    private RollbackRewardAction $rollbackAction;
    private RecordRewardHistoryAction $recordHistoryAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->distributeAction    = Mockery::mock(DistributeRewardAction::class);
        $this->rollbackAction      = Mockery::mock(RollbackRewardAction::class);
        $this->recordHistoryAction = Mockery::mock(RecordRewardHistoryAction::class);
    }

    /** @test */
    public function it_returns_recorded_result_when_all_stages_pass(): void
    {
        $request = $this->makeRequest();
        $context = $this->makeContext();

        $calculated = new CalculatedReward(
            rewardType:     RewardType::Coins,
            idempotencyKey: $request->idempotencyKey,
            userId:         $request->userId,
            finalCoins:     500,
        );

        $distributed = new DistributionResult(
            rewardType:     RewardType::Coins,
            status:         RewardStatus::Distributed,
            idempotencyKey: $request->idempotencyKey,
            userId:         $request->userId,
            coinsGranted:   500,
        );

        $this->distributeAction->shouldReceive('calculateOnly')->once()->andReturn($calculated);
        $this->distributeAction->shouldReceive('execute')->once()->andReturn($distributed);
        $this->recordHistoryAction->shouldReceive('execute')->once();

        $pipeline = new RewardPipeline(
            validators:          [],
            distributeAction:    $this->distributeAction,
            rollbackAction:      $this->rollbackAction,
            recordHistoryAction: $this->recordHistoryAction,
        );

        $result = $pipeline->execute($request, $context);

        $this->assertTrue($result->succeeded());
        $this->assertEquals(RewardStatus::Recorded, $result->status);
        $this->assertEquals(500, $result->totalCoinsGranted);
    }

    /** @test */
    public function it_returns_failed_result_when_validator_rejects(): void
    {
        $request = $this->makeRequest();
        $context = $this->makeContext();

        $validator = Mockery::mock(RewardValidatorContract::class);
        $validator->shouldReceive('validate')->once()->andThrow(
            RewardValidationException::duplicate('test:key')
        );

        $pipeline = new RewardPipeline(
            validators:          [$validator],
            distributeAction:    $this->distributeAction,
            rollbackAction:      $this->rollbackAction,
            recordHistoryAction: $this->recordHistoryAction,
        );

        $result = $pipeline->execute($request, $context);

        $this->assertTrue($result->failed());
        $this->assertEquals(RewardStatus::Failed, $result->status);
        $this->assertEquals(ValidationFailureReason::Duplicate->value, $result->failureReason);
    }

    /** @test */
    public function it_runs_validators_in_order_and_stops_on_first_failure(): void
    {
        $request = $this->makeRequest();
        $context = $this->makeContext();

        $first  = Mockery::mock(RewardValidatorContract::class);
        $second = Mockery::mock(RewardValidatorContract::class);

        $first->shouldReceive('validate')->once()->andThrow(
            RewardValidationException::userBanned(1)
        );
        $second->shouldReceive('validate')->never();

        $pipeline = new RewardPipeline(
            validators:          [$first, $second],
            distributeAction:    $this->distributeAction,
            rollbackAction:      $this->rollbackAction,
            recordHistoryAction: $this->recordHistoryAction,
        );

        $result = $pipeline->execute($request, $context);

        $this->assertTrue($result->failed());
    }

    /** @test */
    public function dry_run_does_not_call_record_history(): void
    {
        $request = RewardRequest::make(
            userId:     1,
            rewardType: RewardType::Coins,
            source:     RewardSource::Mission,
            sourceId:   '1',
            dryRun:     true,
        );
        $context = $this->makeContext();

        $calculated = new CalculatedReward(
            rewardType:     RewardType::Coins,
            idempotencyKey: $request->idempotencyKey,
            userId:         1,
            isDryRun:       true,
        );
        $distributed = new DistributionResult(
            rewardType:     RewardType::Coins,
            status:         RewardStatus::Validated,
            idempotencyKey: $request->idempotencyKey,
            userId:         1,
        );

        $this->distributeAction->shouldReceive('calculateOnly')->once()->andReturn($calculated);
        $this->distributeAction->shouldReceive('execute')->once()->andReturn($distributed);
        $this->recordHistoryAction->shouldReceive('execute')->never();

        $pipeline = new RewardPipeline(
            validators:          [],
            distributeAction:    $this->distributeAction,
            rollbackAction:      $this->rollbackAction,
            recordHistoryAction: $this->recordHistoryAction,
        );

        $result = $pipeline->execute($request, $context);

        $this->assertNotNull($result);
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

    private function makeContext(): RewardContext
    {
        return Mockery::mock(RewardContext::class);
    }
}
