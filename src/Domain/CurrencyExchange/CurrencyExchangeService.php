<?php

namespace Domain\CurrencyExchange;

use Domain\User\User;

class CurrencyExchangeService
{
    public function __construct(private ExchangeRateProvider $rateProvider, private FeeCalculator $feeCalculator)
    {
        $this->rateProvider = $rateProvider;
        $this->feeCalculator = $feeCalculator;
    }

    public function exchange(string $fromCurrency, string $toCurrency, int $amount, User $user, bool $isSelling): void
    {
        $amount = $amount * User::PRECISION_MULTIPLIER;

        $rate = $this->rateProvider->getRate($fromCurrency, $toCurrency);
        $exchangedAmount = (int)($amount * $rate);
        $fee = $this->feeCalculator->calculateFee($exchangedAmount);

        if ($isSelling) {
            if ($user->getPreciseAmountInWallet($fromCurrency) < $amount) {
                throw new \InvalidArgumentException("User doesn't have enough {$fromCurrency} to perform this operation.");
            }

            $user->changeAmountInWallet($fromCurrency, -$amount);
            $user->changeAmountInWallet($toCurrency, $exchangedAmount-$fee);

            return;
        }

        if ($user->getPreciseAmountInWallet($fromCurrency) < $exchangedAmount) {
            throw new \InvalidArgumentException("User doesn't have enough {$fromCurrency} to perform this operation.");
        }
        if ($user->getPreciseAmountInWallet($fromCurrency) < $exchangedAmount+$fee) {
            throw new \InvalidArgumentException("User doesn't have enough {$fromCurrency} to perform this operation and pay the fee.");
        }
        $user->changeAmountInWallet($fromCurrency, -($exchangedAmount+$fee));
        $user->changeAmountInWallet($toCurrency, $amount);
        
        return;
    }
}
