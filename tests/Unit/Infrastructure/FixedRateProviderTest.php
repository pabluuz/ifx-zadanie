<?php

use PHPUnit\Framework\TestCase;
use Infrastructure\CurrencyExchange\FixedRateProvider;

class FixedRateProviderTest extends TestCase
{
    public function testGetRateForExistingCurrencies(): void
    {
        $rateProvider = new FixedRateProvider();
        $rate = $rateProvider->getRate('EUR', 'GBP');
        $this->assertEquals(1.5678, $rate);
    }

    public function testGetRateForNonExistingCurrencies(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $rateProvider = new FixedRateProvider();
        $rateProvider->getRate('USD', 'EUR');
    }
}
