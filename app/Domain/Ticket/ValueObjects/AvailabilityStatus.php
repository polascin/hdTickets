<?php declare(strict_types=1);

namespace App\Domain\Ticket\ValueObjects;

use InvalidArgumentException;

use function in_array;
use function sprintf;

final readonly class AvailabilityStatus
{
    public const AVAILABLE = 'AVAILABLE';

    public const LIMITED = 'LIMITED';

    public const SOLD_OUT = 'SOLD_OUT';

    public const ON_SALE_SOON = 'ON_SALE_SOON';

    public const UNKNOWN = 'UNKNOWN';

    private const VALID_STATUSES = [
        self::AVAILABLE,
        self::LIMITED,
        self::SOLD_OUT,
        self::ON_SALE_SOON,
        self::UNKNOWN,
    ];

    public function __construct(
        private string $status,
    ) {
        $this->validate($status);
    }

    public function value(): string
    {
        return strtoupper($this->status);
    }

    public function isAvailable(): bool
    {
        return $this->value() === self::AVAILABLE;
    }

    public function isLimited(): bool
    {
        return $this->value() === self::LIMITED;
    }

    public function isSoldOut(): bool
    {
        return $this->value() === self::SOLD_OUT;
    }

    public function isOnSaleSoon(): bool
    {
        return $this->value() === self::ON_SALE_SOON;
    }

    public function isUnknown(): bool
    {
        return $this->value() === self::UNKNOWN;
    }

    public function canPurchase(): bool
    {
        return in_array($this->value(), [self::AVAILABLE, self::LIMITED], TRUE);
    }

    public function displayName(): string
    {
        return match ($this->value()) {
            self::AVAILABLE    => 'Available',
            self::LIMITED      => 'Limited Availability',
            self::SOLD_OUT     => 'Sold Out',
            self::ON_SALE_SOON => 'On Sale Soon',
            self::UNKNOWN      => 'Unknown',
        };
    }

    public function equals(self $other): bool
    {
        return $this->value() === $other->value();
    }

    public static function validStatuses(): array
    {
        return self::VALID_STATUSES;
    }

    public static function available(): self
    {
        return new self(self::AVAILABLE);
    }

    public static function limited(): self
    {
        return new self(self::LIMITED);
    }

    public static function soldOut(): self
    {
        return new self(self::SOLD_OUT);
    }

    public static function onSaleSoon(): self
    {
        return new self(self::ON_SALE_SOON);
    }

    public static function unknown(): self
    {
        return new self(self::UNKNOWN);
    }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

    private function validate(string $status): void
    {
        if (empty(trim($status))) {
            throw new InvalidArgumentException('Availability status cannot be empty');
        }

        $normalizedStatus = strtoupper(trim($status));
        if (! in_array($normalizedStatus, self::VALID_STATUSES, TRUE)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid availability status: %s. Valid statuses: %s',
                    $status,
                    implode(', ', self::VALID_STATUSES),
                ),
            );
        }
    }

    public function __toString(): string
    {
        return $this->displayName();
    }
}
