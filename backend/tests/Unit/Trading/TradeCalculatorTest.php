<?php

declare(strict_types=1);

namespace Tests\Unit\Trading;

use App\Trading\Calculators\TradeCalculator;
use Tests\TestCase;

/**
 * @group trading
 * @group calculators
 */
final class TradeCalculatorTest extends TestCase
{
    public function test_total_value_calculation(): void
    {
        $value = TradeCalculator::totalValue(10, 15000); // 10 shares @ ₹150.00
        $this->assertEquals(150000, $value);
    }

    public function test_brokerage_is_zero(): void
    {
        $brokerage = TradeCalculator::brokerage(150000);
        $this->assertEquals(0, $brokerage);
    }

    public function test_tax_calculation(): void
    {
        // 0.1% GST + STT on ₹1500.00 (150,000 paise) = 150 paise (₹1.50)
        $tax = TradeCalculator::tax(150000);
        $this->assertEquals(150, $tax);
    }

    public function test_transaction_fees_calculation(): void
    {
        // 0.003% fee on ₹1500.00 (150,000 paise) = 4 paise
        $fees = TradeCalculator::transactionFees(150000);
        $this->assertEquals(4, $fees);
    }

    public function test_realized_pnl_calculation(): void
    {
        // Buy at ₹100.00, sell at ₹120.00 for 5 shares
        // Cost: 5 * 10000 = 50000 paise
        // Proceeds: 5 * 12000 = 60000 paise
        // Realized P&L = +10000 paise
        $pnl = TradeCalculator::realizedPnl(5, 12000, 10000);
        $this->assertEquals(10000, $pnl);
    }
}
