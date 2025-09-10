<?php declare(strict_types=1);

namespace App\Domain\Ticket\ValueObjects;

use InvalidArgumentException;
use Stringable;

use function in_array;
use function sprintf;

final readonly class AvailabilityStatus implements Stringable
{
    public const string AVAILABLE = 'AVAILABLE';

    public const string LIMITED = 'LIMITED';

    public const string SOLD_OUT = 'SOLD_OUT';

    public const string ON_SALE_SOON = 'ON_SALE_SOON';

    public const string UNKNOWN = 'UNKNOWN';

    private const array VALID_STATUSES = [
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

    /**
     * Value
     */
    public function value(): string
    {
        return strtoupper($this->status);
    }

    /**
     * Check if  available
     */
    public function isAvailable(): bool
    {
        return $this->value() === self::AVAILABLE;
    }

    /**
     * Check if  limited
     */
    public function isLimited(): bool
    {
        return $this->value() === self::LIMITED;
    }

    /**
     * Check if  sold out
     */
    public function isSoldOut(): bool
    {
        return $this->value() === self::SOLD_OUT;
    }

    /**
     * Check if  on sale soon
     */
    public function isOnSaleSoon(): bool
    {
        return $this->value() === self::ON_SALE_SOON;
    }

    /**
     * Check if  unknown
     */
    public function isUnknown(): bool
    {
        return $this->value() === self::UNKNOWN;
    }

    /**
     * Check if can  purchase
     */
    public function canPurchase(): bool
    {
        return in_array($this->value(), [self::AVAILABLE, self::LIMITED], TRUE);
    }

    /**
     * DisplayName
     */
    public function displayName(): string
    {
        return match ($this->value()) {
            self::AVAILABLE    => 'Available',
            self::LIMITED      => 'Limited Availability',
            self::SOLD_OUT     => 'Sold Out',
            self::ON_SALE_SOON => 'On Sale Soon',
            self::UNKNOWN      => 'Unknown',
            default            => 'Unknown',
        };
    }

    /**
     * Equals
     */
    public function equals(self $other): bool
    {
        return $this->value() === $other->value();
    }

    /**
     * @return array<int, string>
     */
    /**
     * ValidStatuses
     */
    public static function validStatuses(): array
    {
        return self::VALID_STATUSES;
    }

    /**
     * Available
     */
    public static function available(): self
    {
        return new self(self::AVAILABLE);
    }

    /**
     * Limited
     */
    public static function limited(): self
    {
        return new self(self::LIMITED);
    }

    /**
     * SoldOut
     */
    public static function soldOut(): self
    {
        return new self(self::SOLD_OUT);
    }

    /**
     * OnSaleSoon
     */
    public static function onSaleSoon(): self
    {
        return new self(self::ON_SALE_SOON);
    }

    /**
     * Unknown
     */
    public static function unknown(): self
    {
        return new self(self::UNKNOWN);
    }

    /**
     * FromString
     */
    public static function fromString(string $status): self
    {
        return new self($status);
    }

    /**
     * Validate
     */
    private function validate(string $status): void
    {
        if (in_array(trim($status), ['', '0'], TRUE)) {
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

    /**
     * __toString
     */
    public function __toString(): string
    {
        return $this->displayName();
    }
}
