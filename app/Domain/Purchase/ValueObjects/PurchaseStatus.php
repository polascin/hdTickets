<?php declare(strict_types=1);

namespace App\Domain\Purchase\ValueObjects;

use InvalidArgumentException;

use function in_array;
use function sprintf;

final readonly class PurchaseStatus
{
    public const PENDING = 'PENDING';

    public const QUEUED = 'QUEUED';

    public const PROCESSING = 'PROCESSING';

    public const COMPLETED = 'COMPLETED';

    public const FAILED = 'FAILED';

    public const CANCELLED = 'CANCELLED';

    public const REFUNDED = 'REFUNDED';

    private const VALID_STATUSES = [
        self::PENDING,
        self::QUEUED,
        self::PROCESSING,
        self::COMPLETED,
        self::FAILED,
        self::CANCELLED,
        self::REFUNDED,
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
     * Check if  pending
     */
    public function isPending(): bool
    {
        return $this->value() === self::PENDING;
    }

    /**
     * Check if  queued
     */
    public function isQueued(): bool
    {
        return $this->value() === self::QUEUED;
    }

    /**
     * Check if  processing
     */
    public function isProcessing(): bool
    {
        return $this->value() === self::PROCESSING;
    }

    /**
     * Check if  completed
     */
    public function isCompleted(): bool
    {
        return $this->value() === self::COMPLETED;
    }

    /**
     * Check if  failed
     */
    public function isFailed(): bool
    {
        return $this->value() === self::FAILED;
    }

    /**
     * Check if  cancelled
     */
    public function isCancelled(): bool
    {
        return $this->value() === self::CANCELLED;
    }

    /**
     * Check if  refunded
     */
    public function isRefunded(): bool
    {
        return $this->value() === self::REFUNDED;
    }

    /**
     * Check if can  cancel
     */
    public function canCancel(): bool
    {
        return in_array($this->value(), [self::PENDING, self::QUEUED], TRUE);
    }

    /**
     * Check if can  refund
     */
    public function canRefund(): bool
    {
        return $this->value() === self::COMPLETED;
    }

    /**
     * Check if  active
     */
    public function isActive(): bool
    {
        return in_array($this->value(), [
            self::PENDING,
            self::QUEUED,
            self::PROCESSING,
        ], TRUE);
    }

    /**
     * Check if  final
     */
    public function isFinal(): bool
    {
        return in_array($this->value(), [
            self::COMPLETED,
            self::FAILED,
            self::CANCELLED,
            self::REFUNDED,
        ], TRUE);
    }

    /**
     * DisplayName
     */
    public function displayName(): string
    {
        return match ($this->value()) {
            self::PENDING    => 'Pending',
            self::QUEUED     => 'Queued',
            self::PROCESSING => 'Processing',
            self::COMPLETED  => 'Completed',
            self::FAILED     => 'Failed',
            self::CANCELLED  => 'Cancelled',
            self::REFUNDED   => 'Refunded',
            default          => 'Unknown',
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
     * @return array<string, string>
     */
    /**
     * ValidStatuses
     */
    public static function validStatuses(): array
    {
        return array_combine(
            self::VALID_STATUSES,
            array_map(
                fn (string $status) => match ($status) {
                    self::PENDING    => 'Pending',
                    self::QUEUED     => 'Queued',
                    self::PROCESSING => 'Processing',
                    self::COMPLETED  => 'Completed',
                    self::FAILED     => 'Failed',
                    self::CANCELLED  => 'Cancelled',
                    self::REFUNDED   => 'Refunded',
                },
                self::VALID_STATUSES,
            ),
        );
    }

    /**
     * Pending
     */
    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    /**
     * Queued
     */
    public static function queued(): self
    {
        return new self(self::QUEUED);
    }

    /**
     * Processing
     */
    public static function processing(): self
    {
        return new self(self::PROCESSING);
    }

    /**
     * Completed
     */
    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    /**
     * Failed
     */
    public static function failed(): self
    {
        return new self(self::FAILED);
    }

    /**
     * Check if can celled
     */
    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    /**
     * Refunded
     */
    public static function refunded(): self
    {
        return new self(self::REFUNDED);
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
        if (empty(trim($status))) {
            throw new InvalidArgumentException('Purchase status cannot be empty');
        }

        $normalizedStatus = strtoupper(trim($status));
        if (! in_array($normalizedStatus, self::VALID_STATUSES, TRUE)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid purchase status: %s. Valid statuses: %s',
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
