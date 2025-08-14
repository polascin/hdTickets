<?php declare(strict_types=1);

namespace App\Domain\Event\ValueObjects;

use InvalidArgumentException;

use function strlen;

final readonly class EventId
{
    public function __construct(
        private string $value,
    ) {
        $this->validate($value);
    }

    /**
     * Value
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Equals
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * FromString
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Generate
     */
    public static function generate(): self
    {
        return new self('event_' . str_replace('.', '_', uniqid('', TRUE)));
    }

    /**
     * Validate
     */
    private function validate(string $value): void
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('Event ID cannot be empty');
        }

        if (strlen($value) > 255) {
            throw new InvalidArgumentException('Event ID must be a valid string with max 255 characters');
        }

        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            throw new InvalidArgumentException('Event ID must contain only alphanumeric characters, underscores, and hyphens');
        }
    }

    /**
     * __toString
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
