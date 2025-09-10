<?php declare(strict_types=1);

namespace App\Domain\Event\ValueObjects;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use Stringable;

final readonly class EventDate implements Stringable
{
    public function __construct(
        private DateTimeImmutable $value,
    ) {
        $this->validate($value);
    }

    /**
     * Value
     */
    public function value(): DateTimeImmutable
    {
        return $this->value;
    }

    /**
     * Format
     */
    public function format(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->value->format($format);
    }

    /**
     * Check if  upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->value > new DateTimeImmutable();
    }

    /**
     * Check if  past
     */
    public function isPast(): bool
    {
        return $this->value < new DateTimeImmutable();
    }

    /**
     * Equals
     */
    public function equals(self $other): bool
    {
        return $this->value->getTimestamp() === $other->value->getTimestamp();
    }

    /**
     * FromString
     */
    public static function fromString(string $dateString): self
    {
        try {
            return new self(new DateTimeImmutable($dateString));
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid date string provided: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Now
     */
    public static function now(): self
    {
        return new self(new DateTimeImmutable());
    }

    /**
     * Validate
     */
    private function validate(DateTimeImmutable $value): void
    {
        $now = new DateTimeImmutable();
        if ($value < $now->modify('-1 year')) {
            throw new InvalidArgumentException('Event date cannot be more than 1 year in the past');
        }

        if ($value > $now->modify('+5 years')) {
            throw new InvalidArgumentException('Event date cannot be more than 5 years in the future');
        }
    }

    /**
     * __toString
     */
    public function __toString(): string
    {
        return $this->format();
    }
}
