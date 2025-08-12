<?php declare(strict_types=1);

namespace App\Domain\Ticket\Entities;

use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Ticket\ValueObjects\AvailabilityStatus;
use App\Domain\Ticket\ValueObjects\PlatformSource;
use App\Domain\Ticket\ValueObjects\Price;
use App\Domain\Ticket\ValueObjects\TicketId;
use DateTimeImmutable;
use InvalidArgumentException;

use function sprintf;
use function strlen;

class MonitoredTicket
{
    /** @var array<int, object> */
    private array $domainEvents = [];

    public function __construct(
        private TicketId $id,
        private EventId $eventId,
        private string $section,
        private string $row,
        private string $seat,
        private Price $price,
        private AvailabilityStatus $availabilityStatus,
        private PlatformSource $source,
        private ?string $description = NULL,
        private ?DateTimeImmutable $lastMonitoredAt = NULL,
        private ?DateTimeImmutable $createdAt = NULL,
        private ?DateTimeImmutable $updatedAt = NULL,
    ) {
        $this->lastMonitoredAt = $lastMonitoredAt ?? new DateTimeImmutable();
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
        $this->validate();
    }

    public function getId(): TicketId
    {
        return $this->id;
    }

    public function getEventId(): EventId
    {
        return $this->eventId;
    }

    public function getSection(): string
    {
        return $this->section;
    }

    public function getRow(): string
    {
        return $this->row;
    }

    public function getSeat(): string
    {
        return $this->seat;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getAvailabilityStatus(): AvailabilityStatus
    {
        return $this->availabilityStatus;
    }

    public function getSource(): PlatformSource
    {
        return $this->source;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLastMonitoredAt(): DateTimeImmutable
    {
        return $this->lastMonitoredAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updatePrice(Price $newPrice): void
    {
        if (! $this->price->equals($newPrice)) {
            $oldPrice = $this->price;
            $this->price = $newPrice;
            $this->updatedAt = new DateTimeImmutable();

            $this->recordDomainEvent(
                new \App\Domain\Ticket\Events\TicketPriceChanged(
                    $this->id,
                    $oldPrice,
                    $newPrice,
                ),
            );
        }
    }

    public function updateAvailability(AvailabilityStatus $newStatus): void
    {
        if (! $this->availabilityStatus->equals($newStatus)) {
            $oldStatus = $this->availabilityStatus;
            $this->availabilityStatus = $newStatus;
            $this->updatedAt = new DateTimeImmutable();

            $this->recordDomainEvent(
                new \App\Domain\Ticket\Events\TicketAvailabilityChanged(
                    $this->id,
                    $oldStatus,
                    $newStatus,
                ),
            );
        }
    }

    public function updateMonitoringTimestamp(): void
    {
        $this->lastMonitoredAt = new DateTimeImmutable();
    }

    public function isAvailable(): bool
    {
        return $this->availabilityStatus->canPurchase();
    }

    public function isFromOfficialSource(): bool
    {
        return $this->source->isOfficial();
    }

    public function getLocationDescription(): string
    {
        return trim(sprintf('%s %s %s', $this->section, $this->row, $this->seat));
    }

    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }

    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }

    private function validate(): void
    {
        if (empty(trim($this->section))) {
            throw new InvalidArgumentException('Section cannot be empty');
        }

        if (empty(trim($this->row))) {
            throw new InvalidArgumentException('Row cannot be empty');
        }

        if (empty(trim($this->seat))) {
            throw new InvalidArgumentException('Seat cannot be empty');
        }

        if (strlen($this->section) > 50) {
            throw new InvalidArgumentException('Section name cannot exceed 50 characters');
        }

        if (strlen($this->row) > 20) {
            throw new InvalidArgumentException('Row name cannot exceed 20 characters');
        }

        if (strlen($this->seat) > 20) {
            throw new InvalidArgumentException('Seat name cannot exceed 20 characters');
        }

        if ($this->description !== NULL && strlen($this->description) > 500) {
            throw new InvalidArgumentException('Description cannot exceed 500 characters');
        }
    }

    private function recordDomainEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}
