<?php

namespace App\Domain\Investment;

use InvalidArgumentException;

final class Money
{
    private const MONTHLY_RATE_NUMERATOR = 10052;

    private const MONTHLY_RATE_DENOMINATOR = 10000;

    private function __construct(private readonly int $cents) {}

    public static function fromCents(int $cents): self
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }

        return new self($cents);
    }

    public static function fromDecimalString(string $amount): self
    {
        if (! preg_match('/^(\d+)\.(\d{2})$/', $amount, $matches)) {
            throw new InvalidArgumentException('Amount must be a non-negative decimal with exactly two places.');
        }

        return self::fromCents(((int) $matches[1] * 100) + (int) $matches[2]);
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function toDecimalString(): string
    {
        return sprintf('%d.%02d', intdiv($this->cents, 100), $this->cents % 100);
    }

    public function applyMonthlyCompoundRate(): self
    {
        return self::fromCents($this->multiplyAndRoundHalfUp(
            self::MONTHLY_RATE_NUMERATOR,
            self::MONTHLY_RATE_DENOMINATOR,
        ));
    }

    public function percentageOf(int $basisPoints): self
    {
        return self::fromCents($this->multiplyAndRoundHalfUp($basisPoints, 10_000));
    }

    public function minus(self $other): self
    {
        $difference = $this->cents - $other->cents;

        return self::fromCents(max(0, $difference));
    }

    private function multiplyAndRoundHalfUp(int $numerator, int $denominator): int
    {
        $product = $this->cents * $numerator;
        $half = intdiv($denominator, 2);

        return intdiv($product + $half, $denominator);
    }
}
