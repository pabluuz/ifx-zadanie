<?php

namespace Domain\CurrencyExchange;

interface ExchangeRateProvider
{
    public function getRate(string $fromCurrency, string $toCurrency): float;
}
