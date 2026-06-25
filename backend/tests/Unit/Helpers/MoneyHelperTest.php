<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\MoneyHelper;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Paper Trading Tycoon — MoneyHelper Unit Tests
 *
 * Critical: Monetary calculation bugs directly impact user balances.
 * Every MoneyHelper function must have complete unit test coverage.
 */
final class MoneyHelperTest extends TestCase
{
    public function test_rupees_to_paise_converts_correctly(): void
    {
        $this->assertSame(100000000, MoneyHelper::rupeesToPaise(1000000.00));
        $this->assertSame(100, MoneyHelper::rupeesToPaise(1.00));
        $this->assertSame(0, MoneyHelper::rupeesToPaise(0.00));
    }

    public function test_paise_to_rupees_converts_correctly(): void
    {
        $this->assertSame('10000.00', MoneyHelper::paiseToRupees(1000000));
        $this->assertSame('1.00', MoneyHelper::paiseToRupees(100));
        $this->assertSame('0.00', MoneyHelper::paiseToRupees(0));
    }

    public function test_add_sums_paise_correctly(): void
    {
        $this->assertSame(1500, MoneyHelper::add(1000, 500));
        $this->assertSame(0, MoneyHelper::add(0, 0));
    }

    public function test_subtract_computes_paise_correctly(): void
    {
        $this->assertSame(500, MoneyHelper::subtract(1000, 500));
        $this->assertSame(-500, MoneyHelper::subtract(500, 1000));
    }

    public function test_multiply_computes_order_value(): void
    {
        // 100 shares at ₹452.50 = ₹45,250 = 4525000 paise
        $this->assertSame(4525000, MoneyHelper::multiply(45250, 100));
    }

    public function test_pl_percentage_calculates_correctly(): void
    {
        // Bought at 100000, now worth 105000 → +5.00%
        $this->assertSame('5.00', MoneyHelper::plPercentage(105000, 100000));
    }

    public function test_pl_percentage_returns_zero_for_zero_cost_basis(): void
    {
        $this->assertSame('0.00', MoneyHelper::plPercentage(100000, 0));
    }

    public function test_assert_positive_throws_for_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MoneyHelper::assertPositive(0);
    }

    public function test_assert_positive_throws_for_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MoneyHelper::assertPositive(-100);
    }

    public function test_assert_positive_passes_for_positive(): void
    {
        MoneyHelper::assertPositive(100);
        $this->assertTrue(true); // No exception thrown.
    }
}
