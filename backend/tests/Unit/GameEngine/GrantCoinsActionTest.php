<?php

declare(strict_types=1);

namespace Tests\Unit\GameEngine;

use App\Enums\CoinTransactionSource;
use App\GameEngine\Actions\GrantCoinsAction;
use App\GameEngine\Contexts\GameContext;
use App\GameEngine\DTOs\RewardResult;
use App\GameEngine\Enums\PlayerState;
use App\GameEngine\Exceptions\RewardException;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for GrantCoinsAction.
 *
 * Coverage targets:
 * - credit() increases balance and writes CoinTransaction
 * - credit() is idempotent — duplicate source_id returns existing record
 * - credit() throws RewardException on amount <= 0
 * - debit() decreases balance and writes negative CoinTransaction
 * - debit() throws RewardException when balance would go negative
 * - debit() throws RewardException on amount <= 0
 * - balanceBefore / balanceAfter are correctly recorded on both CoinTransaction and Wallet
 */
final class GrantCoinsActionTest extends TestCase
{
    use RefreshDatabase;

    private GrantCoinsAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GrantCoinsAction;
    }

    /** @test */
    public function credit_increases_wallet_balance(): void
    {
        $context = $this->makeContext(coinBalance: 100);

        $result = $this->action->credit(
            $context,
            CoinTransactionSource::DailyLogin,
            'login_2025_01_01',
            10,
        );

        $this->assertInstanceOf(RewardResult::class, $result);
        $this->assertSame(10, $result->coinsGranted);
        $this->assertSame(100, $result->balanceBefore);
        $this->assertSame(110, $result->balanceAfter);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $context->userId(),
            'coin_balance' => 110,
        ]);
    }

    /** @test */
    public function credit_writes_coin_transaction_record(): void
    {
        $context = $this->makeContext(coinBalance: 0);

        $this->action->credit(
            $context,
            CoinTransactionSource::Achievement,
            'achievement_5',
            250,
        );

        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $context->userId(),
            'amount' => 250,
            'source_id' => 'achievement_5',
            'balance_after' => 250,
        ]);
    }

    /** @test */
    public function credit_is_idempotent_on_duplicate_source_id(): void
    {
        $context = $this->makeContext(coinBalance: 0);

        $this->action->credit($context, CoinTransactionSource::LevelUp, 'level_5', 200);
        $result = $this->action->credit($context, CoinTransactionSource::LevelUp, 'level_5', 200);

        $this->assertSame(200, $result->coinsGranted);
        $this->assertDatabaseCount('coin_transactions', 1);
    }

    /** @test */
    public function credit_throws_on_zero_amount(): void
    {
        $this->expectException(RewardException::class);

        $this->action->credit(
            $this->makeContext(),
            CoinTransactionSource::DailyLogin,
            'login_zero',
            0,
        );
    }

    /** @test */
    public function debit_decreases_wallet_balance(): void
    {
        $context = $this->makeContext(coinBalance: 500);

        $result = $this->action->debit(
            $context,
            CoinTransactionSource::StorePurchase,
            'purchase_001',
            100,
        );

        $this->assertSame(-100, $result->coinsGranted);
        $this->assertSame(500, $result->balanceBefore);
        $this->assertSame(400, $result->balanceAfter);
    }

    /** @test */
    public function debit_throws_when_balance_insufficient(): void
    {
        $this->expectException(RewardException::class);

        $context = $this->makeContext(coinBalance: 50);

        $this->action->debit(
            $context,
            CoinTransactionSource::StorePurchase,
            'purchase_broke',
            100,
        );
    }

    /** @test */
    public function debit_throws_on_zero_amount(): void
    {
        $this->expectException(RewardException::class);

        $this->action->debit(
            $this->makeContext(coinBalance: 500),
            CoinTransactionSource::StorePurchase,
            'purchase_zero',
            0,
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeContext(int $coinBalance = 0): GameContext
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'coin_balance' => $coinBalance]);
        $userLevel = UserLevel::factory()->create(['user_id' => $user->id]);

        return new GameContext(
            user: $user,
            playerState: PlayerState::Active,
            wallet: $wallet,
            userLevel: $userLevel,
            currentLeague: null,
            league: null,
            activeSeason: null,
            activeMissions: [],
            unlockedAchievementIds: [],
            loginStreakDays: 0,
            activeMultipliers: ['xp' => 1.0, 'coins' => 1.0],
            featureFlags: [],
            builtAt: microtime(true),
        );
    }
}
