<?php declare(strict_types=1);

namespace App\Domain\Event\ValueObjects;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

final readonly class EventDate
{
    public function __construct(
        private DateTimeImmutable $value,
    ) {
        $this->validate($value);
    }

    public function value(): DateTimeImmutable
    {
        return $this->value;
    }

    public function format(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->value->format($format);
    }

    public function isUpcoming(): bool
    {
        return $this->value > new DateTimeImmutable();
    }

    public function isPast(): bool
    {
        return $this->value < new DateTimeImmutable();
    }

    public function equals(self $other): bool
    {
        return $this->value->getTimestamp() === $other->value->getTimestamp();
    }

    public static function fromString(string $dateString): self
    {
        try {
            return new self(new DateTimeImmutable($dateString));
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid date string provided: ' . $e->getMessage());
        }
    }

    public static function now(): self
    {
        return new self(new DateTimeImmutable());
    }

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

    public function __toString(): string
    {
        return $this->format();
    }
}
