<?php

use PHPUnit\Framework\TestCase;
use Infrastructure\CurrencyExchange\PercentageFeeCalculator;

class PercentageFeeCalculatorTest extends TestCase
{
    public function testCalculateFee(): void
    {
        $feeCalculator = new PercentageFeeCalculator();
        $fee = $feeCalculator->calculateFee(1000);
        $this->assertEquals(10.0, $fee);
    }
}
