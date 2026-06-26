<?php

declare(strict_types=1);

namespace Tests\Unit\Trading;

use App\Trading\ValueObjects\Brokerage;
use App\Trading\ValueObjects\ExecutionPrice;
use App\Trading\ValueObjects\Fees;
use App\Trading\ValueObjects\OrderId;
use App\Trading\ValueObjects\OrderPrice;
use App\Trading\ValueObjects\Quantity;
use App\Trading\ValueObjects\Slippage;
use App\Trading\ValueObjects\Tax;
use App\Trading\ValueObjects\TradeAmount;
use App\Trading\ValueObjects\TradeId;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * @group trading
 * @group value-objects
 */
final class ValueObjectsTest extends TestCase
{
    public function test_quantity_must_be_positive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Quantity(0);
    }

    public function test_quantity_valid(): void
    {
        $qty = new Quantity(10);
        $this->assertEquals(10, $qty->value);
        $this->assertTrue($qty->equals(new Quantity(10)));
        $this->assertFalse($qty->equals(new Quantity(5)));
    }

    public function test_order_price_cannot_be_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OrderPrice(-1);
    }

    public function test_order_price_valid(): void
    {
        $price = new OrderPrice(15000); // ₹150.00
        $this->assertEquals(15000, $price->valuePaise);
        $this->assertTrue($price->equals(new OrderPrice(15000)));
    }

    public function test_execution_price_must_be_positive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ExecutionPrice(0);
    }

    public function test_execution_price_valid(): void
    {
        $price = new ExecutionPrice(100);
        $this->assertEquals(100, $price->valuePaise);
    }

    public function test_trade_amount_cannot_be_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TradeAmount(-5);
    }

    public function test_brokerage_cannot_be_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Brokerage(-100);
    }

    public function test_slippage_cannot_be_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Slippage(-1);
    }

    public function test_fees_cannot_be_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Fees(-1);
    }

    public function test_tax_cannot_be_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Tax(-1);
    }

    public function test_order_id_must_be_positive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OrderId(0);
    }

    public function test_trade_id_must_be_positive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TradeId(0);
    }
}
