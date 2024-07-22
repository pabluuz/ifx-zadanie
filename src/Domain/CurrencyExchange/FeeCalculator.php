<?php

namespace Domain\CurrencyExchange;

interface FeeCalculator
{
    public function calculateFee(int $amount): int;
}
