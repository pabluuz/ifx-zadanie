<?php

namespace Domain\CurrencyExchange\Tests;

use Domain\CurrencyExchange\CurrencyExchangeService;
use Domain\CurrencyExchange\ExchangeRateProvider;
use Domain\CurrencyExchange\FeeCalculator;
use Domain\User\User;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class CurrencyExchangeServiceTest extends TestCase
{
    private MockObject $rateProviderMock;
    private MockObject $feeCalculatorMock;
    private CurrencyExchangeService $currencyExchangeService;
    private MockObject $userMock;

    protected function setUp(): void
    {
        $this->rateProviderMock = $this->createMock(ExchangeRateProvider::class);
        $this->feeCalculatorMock = $this->createMock(FeeCalculator::class);
        $this->userMock = $this->createMock(User::class);
        $this->currencyExchangeService = new CurrencyExchangeService($this->rateProviderMock, $this->feeCalculatorMock);
    }

    public function testExchangeSellingEnoughFunds(): void
    {
        $fromCurrency = 'USD';
        $toCurrency = 'EUR';
        $amount = 100;
        $rate = 0.85;
        $exchangedAmount = (int)($amount * $rate) ;
        $fee = 5;

        $this->rateProviderMock->expects($this->once())
            ->method('getRate')
            ->with($fromCurrency, $toCurrency)
            ->willReturn($rate);

        $this->feeCalculatorMock->expects($this->once())
            ->method('calculateFee')
            ->with($exchangedAmount * User::PRECISION_MULTIPLIER)
            ->willReturn($fee * User::PRECISION_MULTIPLIER);

        $this->userMock->expects($this->once())
            ->method('getPreciseAmountInWallet')
            ->willReturn(100 * User::PRECISION_MULTIPLIER);

        $this->userMock->expects($this->exactly(2))
            ->method('changeAmountInWallet')
            ->withConsecutive(
                [$fromCurrency, -$amount * User::PRECISION_MULTIPLIER],
                [$toCurrency, ($exchangedAmount - $fee) * User::PRECISION_MULTIPLIER]
            );

        $this->currencyExchangeService->exchange($fromCurrency, $toCurrency, $amount, $this->userMock, true);
    }

    public function testExchangeSellingInsufficientFunds(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $fromCurrency = 'USD';
        $toCurrency = 'EUR';
        $amount = 100;
        $rate = 0.85;
        $exchangedAmount = (int)($amount * $rate);
        $fee = 5;

        $this->rateProviderMock->expects($this->once())
            ->method('getRate')
            ->with($fromCurrency, $toCurrency)
            ->willReturn($rate);

        $this->feeCalculatorMock->expects($this->once())
            ->method('calculateFee')
            ->with($exchangedAmount * User::PRECISION_MULTIPLIER)
            ->willReturn($fee * User::PRECISION_MULTIPLIER);

        $this->userMock->expects($this->once())
            ->method('getPreciseAmountInWallet')
            ->with($fromCurrency)
            ->willReturn(50 * User::PRECISION_MULTIPLIER);

        $this->currencyExchangeService->exchange($fromCurrency, $toCurrency, $amount, $this->userMock, true);
    }

    public function testExchangeBuyingEnoughFunds(): void
    {
        $fromCurrency = 'USD';
        $toCurrency = 'EUR';
        $amount = 100;
        $rate = 0.85;
        $exchangedAmount = (int)($amount * $rate);
        $fee = 5;

        $this->rateProviderMock->expects($this->once())
            ->method('getRate')
            ->with($fromCurrency, $toCurrency)
            ->willReturn($rate);

        $this->feeCalculatorMock->expects($this->once())
            ->method('calculateFee')
            ->with($exchangedAmount * User::PRECISION_MULTIPLIER)
            ->willReturn($fee * User::PRECISION_MULTIPLIER);

        $this->userMock->expects($this->exactly(2))
            ->method('getPreciseAmountInWallet')
            ->willReturnMap([
                [$fromCurrency, 1000 * User::PRECISION_MULTIPLIER],
                [$toCurrency, 0],
            ]);

        $this->userMock->expects($this->exactly(2))
            ->method('changeAmountInWallet')
            ->withConsecutive(
                [$fromCurrency, -($exchangedAmount + $fee) * User::PRECISION_MULTIPLIER],
                [$toCurrency, $amount * User::PRECISION_MULTIPLIER]
            );

        $this->currencyExchangeService->exchange($fromCurrency, $toCurrency, $amount, $this->userMock, false);
    }

    public function testExchangeBuyingInsufficientFunds(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $fromCurrency = 'USD';
        $toCurrency = 'EUR';
        $amount = 100;
        $rate = 0.85;
        $exchangedAmount = (int)($amount * $rate);
        $fee = 5;

        $this->rateProviderMock->expects($this->once())
            ->method('getRate')
            ->with($fromCurrency, $toCurrency)
            ->willReturn($rate);

        $this->feeCalculatorMock->expects($this->once())
            ->method('calculateFee')
            ->with($exchangedAmount * User::PRECISION_MULTIPLIER)
            ->willReturn($fee * User::PRECISION_MULTIPLIER);

        $this->userMock->expects($this->once())
            ->method('getPreciseAmountInWallet')
            ->with($fromCurrency)
            ->willReturn(50 * User::PRECISION_MULTIPLIER);

        $this->currencyExchangeService->exchange($fromCurrency, $toCurrency, $amount, $this->userMock, false);
    }
}