<?php

use PHPUnit\Framework\TestCase;
use Domain\CurrencyExchange\CurrencyExchangeService;
use Domain\CurrencyExchange\ExchangeFeeService;
use Domain\User\User;
use Infrastructure\CurrencyExchange\FixedRateProvider;
use Infrastructure\CurrencyExchange\PercentageFeeCalculator;

class CurrencyExchangeTest extends TestCase
{
    private CurrencyExchangeService $currencyExchange;

    protected function setUp(): void
    {
        $this->currencyExchange = new CurrencyExchangeService(new FixedRateProvider(), new PercentageFeeCalculator());
    }

    /**
     * Client selling 100 Euros for GBP
     * rate: 100 EUR * 1.5678 = 156.78 GBP
     * price: 156.78 GBP * 0.01 = 1.5678 GBP
     * result: 155.2122 GBP ≈ 155.21 GBP
     */
    public function testClientSellsEurosForGbp(): void
    {
        // Given
        $user = new User([
            'EUR' => 100 * User::PRECISION_MULTIPLIER,
            'GBP' => 0,
        ]);

        // When
        $result = $this->currencyExchange->exchange('EUR', 'GBP', 100, $user, true);

        // Then
        $this->assertEquals(155.21, $user->getRealAmountInWallet('GBP'));
        $this->assertEquals(1552122, $user->getPreciseAmountInWallet('GBP'));
        $this->assertEquals(0, $user->getRealAmountInWallet('EUR'));
    }

    /**
     * Client buying 100 GBP with Euros
     * amount: 100 GBP * 1.5678 = 156.78 GBP
     * fee: 156.78 EUR * 0.01 = 1.5678 EUR
     * resultCost: 156.78 + 1.5678 EUR = 158,34 EUR
     * resuolGiven200: 200 - 158,3478 ≈ 41.65 EUR
     */
    public function testClientBuysGbpWithEuros(): void
    {
        // Given
        $user = new User([
            'EUR' => 200 * User::PRECISION_MULTIPLIER,
            'GBP' => 0,
        ]);

        // When
        $result = $this->currencyExchange->exchange('EUR', 'GBP', 100, $user, false);

        // Then
        $this->assertEquals(100, $user->getRealAmountInWallet('GBP'));
        $this->assertEquals(1000000, $user->getPreciseAmountInWallet('GBP'));
        $this->assertEquals(41.65, $user->getRealAmountInWallet('EUR'));
        $this->assertEquals(416522, $user->getPreciseAmountInWallet('EUR'));
    }

    public function testClientSellsGbpForEuros(): void
    {
        // Given
        $user = new User([
            'EUR' => 0,
            'GBP' => 100 * User::PRECISION_MULTIPLIER,
        ]);

        // When
        $result = $this->currencyExchange->exchange('GBP', 'EUR', 100, $user, true);

        // Then
        $this->assertEquals(152.78, $user->getRealAmountInWallet('EUR'));
        $this->assertEquals(1527768, $user->getPreciseAmountInWallet('EUR'));
        $this->assertEquals(0, $user->getRealAmountInWallet('GBP'));
    }

    public function testClientBuysEurosWithGbp(): void
    {
        // Given
        $user = new User([
            'EUR' => 0,
            'GBP' => 200 * User::PRECISION_MULTIPLIER,
        ]);

        // When
        $result = $this->currencyExchange->exchange('GBP', 'EUR', 100, $user, false);

        // Then
        $this->assertEquals(44.14, $user->getRealAmountInWallet('GBP'));
        $this->assertEquals(441368, $user->getPreciseAmountInWallet('GBP'));
        $this->assertEquals(100, $user->getRealAmountInWallet('EUR'));
        $this->assertEquals(1000000, $user->getPreciseAmountInWallet('EUR'));
    }

    public function testClientBuysGbpWithEurosButHasNotEnoughMoney(): void
    {
        // Given
        $user = new User([
            'EUR' => 100 * User::PRECISION_MULTIPLIER,
            'GBP' => 0,
        ]);

        // Then
        $this->expectException(InvalidArgumentException::class);

        // When
        $result = $this->currencyExchange->exchange('EUR', 'GBP', 100, $user, false);
    }
}
