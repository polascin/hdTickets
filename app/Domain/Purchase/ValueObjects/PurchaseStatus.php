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

    public function value(): string
    {
        return strtoupper($this->status);
    }

    public function isPending(): bool
    {
        return $this->value() === self::PENDING;
    }

    public function isQueued(): bool
    {
        return $this->value() === self::QUEUED;
    }

    public function isProcessing(): bool
    {
        return $this->value() === self::PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this->value() === self::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->value() === self::FAILED;
    }

    public function isCancelled(): bool
    {
        return $this->value() === self::CANCELLED;
    }

    public function isRefunded(): bool
    {
        return $this->value() === self::REFUNDED;
    }

    public function canCancel(): bool
    {
        return in_array($this->value(), [self::PENDING, self::QUEUED], TRUE);
    }

    public function canRefund(): bool
    {
        return $this->value() === self::COMPLETED;
    }

    public function isActive(): bool
    {
        return in_array($this->value(), [
            self::PENDING,
            self::QUEUED,
            self::PROCESSING,
        ], TRUE);
    }

    public function isFinal(): bool
    {
        return in_array($this->value(), [
            self::COMPLETED,
            self::FAILED,
            self::CANCELLED,
            self::REFUNDED,
        ], TRUE);
    }

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

    public function equals(self $other): bool
    {
        return $this->value() === $other->value();
    }

    /**
     * @return array<string, string>
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
                    default          => 'Unknown',
                },
                self::VALID_STATUSES,
            ),
        );
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function queued(): self
    {
        return new self(self::QUEUED);
    }

    public static function processing(): self
    {
        return new self(self::PROCESSING);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function failed(): self
    {
        return new self(self::FAILED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function refunded(): self
    {
        return new self(self::REFUNDED);
    }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

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

    public function __toString(): string
    {
        return $this->displayName();
    }
}
