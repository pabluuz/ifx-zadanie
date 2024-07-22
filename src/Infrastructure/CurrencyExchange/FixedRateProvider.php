<?php

namespace Infrastructure\CurrencyExchange;

use Domain\CurrencyExchange\ExchangeRateProvider;

class FixedRateProvider implements ExchangeRateProvider
{
    private const EXCHANGE_RATES = [
        'EUR_GBP' => 1.5678,
        'GBP_EUR' => 1.5432,
    ];

    public function getRate(string $fromCurrency, string $toCurrency): float
    {
        $rateKey = "{$fromCurrency}_{$toCurrency}";
        if (!isset(self::EXCHANGE_RATES[$rateKey])) {
            throw new \InvalidArgumentException("Exchange rate for {$fromCurrency} to {$toCurrency} not found.");
        }

        return self::EXCHANGE_RATES[$rateKey];
    }
}
