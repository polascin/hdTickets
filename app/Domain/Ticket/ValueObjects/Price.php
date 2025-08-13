<?php declare(strict_types=1);

namespace App\Domain\Ticket\ValueObjects;

use InvalidArgumentException;

use function in_array;

final readonly class Price
{
    public function __construct(
        private float $amount,
        private string $currency = 'GBP',
    ) {
        $this->validate($amount, $currency);
    }

    /**
     * Amount
     */
    public function amount(): float
    {
        return $this->amount;
    }

    /**
     * Currency
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Formatted
     */
    public function formatted(): string
    {
        $symbols = [
            'GBP' => '£',
            'USD' => '$',
            'EUR' => '€',
        ];

        return $symbols[$this->currency] . number_format($this->amount, 2);
    }

    /**
     * Equals
     */
    public function equals(self $other): bool
    {
        return abs($this->amount - $other->amount) < 0.01
               && $this->currency === $other->currency;
    }

    /**
     * Check if  greater than
     */
    public function isGreaterThan(self $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot compare prices with different currencies');
        }

        return $this->amount > $other->amount;
    }

    /**
     * Check if  less than
     */
    public function isLessThan(self $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot compare prices with different currencies');
        }

        return $this->amount < $other->amount;
    }

    /**
     * Add
     */
    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot add prices with different currencies');
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Subtract
     */
    public function subtract(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot subtract prices with different currencies');
        }

        $result = $this->amount - $other->amount;
        if ($result < 0) {
            throw new InvalidArgumentException('Price cannot be negative after subtraction');
        }

        return new self($result, $this->currency);
    }

    /**
     * Percentage
     */
    public function percentage(float $percentage): self
    {
        if ($percentage < 0) {
            throw new InvalidArgumentException('Percentage cannot be negative');
        }

        return new self($this->amount * ($percentage / 100), $this->currency);
    }

    /**
     * Gbp
     */
    public static function gbp(float $amount): self
    {
        return new self($amount, 'GBP');
    }

    /**
     * Usd
     */
    public static function usd(float $amount): self
    {
        return new self($amount, 'USD');
    }

    /**
     * Eur
     */
    public static function eur(float $amount): self
    {
        return new self($amount, 'EUR');
    }

    /**
     * FromString
     */
    public static function fromString(string $amount, string $currency = 'GBP'): self
    {
        $numericAmount = (float) $amount;

        return new self($numericAmount, $currency);
    }

    /**
     * Zero
     */
    public static function zero(string $currency = 'GBP'): self
    {
        return new self(0.0, $currency);
    }

    /**
     * Validate
     */
    private function validate(float $amount, string $currency): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Price amount cannot be negative');
        }

        if ($amount > 999999.99) {
            throw new InvalidArgumentException('Price amount cannot exceed 999,999.99');
        }

        if (is_nan($amount) || is_infinite($amount)) {
            throw new InvalidArgumentException('Price amount must be a valid number');
        }

        if (empty(trim($currency))) {
            throw new InvalidArgumentException('Currency cannot be empty');
        }

        $validCurrencies = ['GBP', 'USD', 'EUR'];
        if (! in_array(strtoupper($currency), $validCurrencies, TRUE)) {
            throw new InvalidArgumentException('Invalid currency. Supported: ' . implode(', ', $validCurrencies));
        }
    }

    /**
     * __toString
     */
    public function __toString(): string
    {
        return $this->formatted();
    }
}
