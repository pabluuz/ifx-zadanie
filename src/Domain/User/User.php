<?php

namespace Domain\User;

/**
 * Normally I'd move wallet to its own class, add validations, but for the scope of this exercise, I'm going to keep it simple.
 * Note, to avoid problems with floats I'm going to use integers. Values are in 0.0001, to keep accounting standards.
 */
class User
{
    public const PRECISION_MULTIPLIER = 10000;

    public function __construct(public array $wallet) 
    { 
        $this->wallet = array_map('intval', $wallet);
    }

    public function changeAmountInWallet(string $currency, int $delta): void
    {
        if (array_key_exists($currency, $this->wallet)) {
            $newAmount = $this->wallet[$currency] + $delta;
            if ($newAmount < 0) {
                throw new \InvalidArgumentException("User doesn't have enough {$currency} to perform this operation.");
            }
            $this->wallet[$currency] = $newAmount;
        } else {
            if ($delta < 0) {
                throw new \InvalidArgumentException("User can't have a negative amount of {$currency} initially.");
            }
            $this->wallet[$currency] = $delta;
        }
    }

    /**
     * For calculation purposes.
     */
    public function getPreciseAmountInWallet(string $currency): int
    {
        return $this->wallet[$currency] ?? 0;
    }

    /**
     *  For display purposes. 
     */
    public function getRealAmountInWallet(string $currency): float
    {
        $amount = ($this->wallet[$currency] ?? 0) / self::PRECISION_MULTIPLIER;
        return (float)number_format($amount, 2, '.', '');
    }
}