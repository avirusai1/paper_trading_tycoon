<?php

declare(strict_types=1);

namespace Tests\Unit\Portfolio;

use App\Models\User;
use App\Models\PortfolioSnapshot;
use App\Portfolio\Contracts\PortfolioServiceContract;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group portfolio
 * @group snapshot
 */
final class SnapshotReconstructionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::query()->create([
            'name' => 'John Doe',
            'email' => 'john.doe4@example.com',
            'password' => bcrypt('secret'),
            'referral_code' => 'JOHNDOE012',
            'status' => 'active',
        ]);
    }

    public function test_snapshot_reconstruction_by_interval(): void
    {
        // Create historical snapshots across multiple dates:
        // Week 1: Day 1 (May 1), Day 2 (May 3)
        // Week 2: Day 3 (May 8), Day 4 (May 10)
        // Month 2: Day 5 (June 1)
        
        $s1 = PortfolioSnapshot::query()->create([
            'user_id' => $this->user->id,
            'virtual_cash_paise' => 100000000,
            'holdings_value_paise' => 10000000,
            'total_portfolio_value_paise' => 110000000,
            'total_pnl_paise' => 10000000,
            'total_pnl_percent' => 10.0,
            'total_holdings_count' => 1,
            'snapshot_date' => '2026-05-01',
            'snapshot_type' => 'daily',
            'taken_at' => Carbon::parse('2026-05-01 16:00:00'),
        ]);

        $s2 = PortfolioSnapshot::query()->create([
            'user_id' => $this->user->id,
            'virtual_cash_paise' => 100000000,
            'holdings_value_paise' => 12000000,
            'total_portfolio_value_paise' => 112000000,
            'total_pnl_paise' => 12000000,
            'total_pnl_percent' => 12.0,
            'total_holdings_count' => 1,
            'snapshot_date' => '2026-05-03',
            'snapshot_type' => 'daily',
            'taken_at' => Carbon::parse('2026-05-03 16:00:00'),
        ]);

        $s3 = PortfolioSnapshot::query()->create([
            'user_id' => $this->user->id,
            'virtual_cash_paise' => 90000000,
            'holdings_value_paise' => 25000000,
            'total_portfolio_value_paise' => 115000000,
            'total_pnl_paise' => 15000000,
            'total_pnl_percent' => 15.0,
            'total_holdings_count' => 2,
            'snapshot_date' => '2026-05-08',
            'snapshot_type' => 'daily',
            'taken_at' => Carbon::parse('2026-05-08 16:00:00'),
        ]);

        $s4 = PortfolioSnapshot::query()->create([
            'user_id' => $this->user->id,
            'virtual_cash_paise' => 90000000,
            'holdings_value_paise' => 28000000,
            'total_portfolio_value_paise' => 118000000,
            'total_pnl_paise' => 18000000,
            'total_pnl_percent' => 18.0,
            'total_holdings_count' => 2,
            'snapshot_date' => '2026-05-10',
            'snapshot_type' => 'daily',
            'taken_at' => Carbon::parse('2026-05-10 16:00:00'),
        ]);

        $s5 = PortfolioSnapshot::query()->create([
            'user_id' => $this->user->id,
            'virtual_cash_paise' => 80000000,
            'holdings_value_paise' => 45000000,
            'total_portfolio_value_paise' => 125000000,
            'total_pnl_paise' => 25000000,
            'total_pnl_percent' => 25.0,
            'total_holdings_count' => 3,
            'snapshot_date' => '2026-06-01',
            'snapshot_type' => 'daily',
            'taken_at' => Carbon::parse('2026-06-01 16:00:00'),
        ]);

        $service = $this->app->make(PortfolioServiceContract::class);

        // 1. Reconstruct history on a daily level (all points)
        $daily = $service->reconstructHistory(
            userId: $this->user->id,
            interval: 'daily',
            startDate: Carbon::parse('2026-04-30'),
            endDate: Carbon::parse('2026-06-05')
        );
        $this->assertCount(5, $daily);

        // 2. Reconstruct weekly (groups weeks and picks end-of-week snapshot)
        // 2026-05-01 (Friday) and 2026-05-03 (Sunday) are in Week 17 (or depending on Carbon startOfWeek).
        // 2026-05-08 and 2026-05-10 are in another week.
        // 2026-06-01 is in a separate week.
        // There should be 3 unique weeks.
        $weekly = $service->reconstructHistory(
            userId: $this->user->id,
            interval: 'weekly',
            startDate: Carbon::parse('2026-04-30'),
            endDate: Carbon::parse('2026-06-05')
        );
        $this->assertCount(3, $weekly);
        // Last point of week 1 should be $s2 (112,000,000 paise value)
        $this->assertEquals(112000000, $weekly[0]['total_value_paise']);
        // Last point of week 2 should be $s4 (118,000,000 paise value)
        $this->assertEquals(118000000, $weekly[1]['total_value_paise']);

        // 3. Reconstruct monthly (groups by month and picks end-of-month snapshot)
        // May: s1, s2, s3, s4 -> picks s4 (118,000,000)
        // June: s5 -> picks s5 (125,000,000)
        $monthly = $service->reconstructHistory(
            userId: $this->user->id,
            interval: 'monthly',
            startDate: Carbon::parse('2026-04-30'),
            endDate: Carbon::parse('2026-06-05')
        );
        $this->assertCount(2, $monthly);
        $this->assertEquals(118000000, $monthly[0]['total_value_paise']);
        $this->assertEquals(125000000, $monthly[1]['total_value_paise']);
    }
}
