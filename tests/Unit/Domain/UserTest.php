<?php

use Domain\User\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testConstructorInitializesWallet(): void
    {
        $wallet = ['USD' => 10000, 'EUR' => 5000];
        $user = new User($wallet);
        
        $this->assertSame($wallet, $user->wallet);
    }

    public function testChangeAmountInWalletIncrease(): void
    {
        $wallet = ['USD' => 10000];
        $user = new User($wallet);
        $user->changeAmountInWallet('USD', 5000);
        
        $this->assertSame(15000, $user->wallet['USD']);
    }

    public function testChangeAmountInWalletDecrease(): void
    {
        $wallet = ['USD' => 10000];
        $user = new User($wallet);
        $user->changeAmountInWallet('USD', -5000);
        
        $this->assertSame(5000, $user->wallet['USD']);
    }

    public function testChangeAmountInWalletThrowsExceptionWhenInsufficientFunds(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $wallet = ['USD' => 10000];
        $user = new User($wallet);
        $user->changeAmountInWallet('USD', -15000);
    }

    public function testChangeAmountInWalletAddsNewCurrency(): void
    {
        $wallet = ['USD' => 10000];
        $user = new User($wallet);
        $user->changeAmountInWallet('EUR', 5000);
        
        $this->assertSame(5000, $user->wallet['EUR']);
    }

    public function testChangeAmountInWalletThrowsExceptionWhenNegativeInitialAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $wallet = ['USD' => 10000];
        $user = new User($wallet);
        $user->changeAmountInWallet('EUR', -5000);
    }

    public function testGetPreciseAmountInWalletReturnsCorrectAmount(): void
    {
        $wallet = ['USD' => 10000];
        $user = new User($wallet);
        
        $this->assertSame(10000, $user->getPreciseAmountInWallet('USD'));
        $this->assertSame(0, $user->getPreciseAmountInWallet('EUR'));
    }

    public function testGetRealAmountInWalletReturnsCorrectAmount(): void
    {
        $wallet = ['USD' => 10000];
        $user = new User($wallet);
        
        $this->assertSame(1.00, $user->getRealAmountInWallet('USD'));
        $this->assertSame(0.00, $user->getRealAmountInWallet('EUR'));
    }
}