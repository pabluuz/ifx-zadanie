<?php

namespace Infrastructure\CurrencyExchange;

use Domain\CurrencyExchange\FeeCalculator;

class PercentageFeeCalculator implements FeeCalculator
{
    private const FEE_PERCENTAGE = 0.01;

    public function calculateFee(int $amount): int
    {
        return (int)($amount * self::FEE_PERCENTAGE);
    }
}
