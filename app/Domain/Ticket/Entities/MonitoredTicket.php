<?php declare(strict_types=1);

namespace App\Domain\Ticket\Entities;

use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Ticket\Events\TicketAvailabilityChanged;
use App\Domain\Ticket\Events\TicketPriceChanged;
use App\Domain\Ticket\ValueObjects\AvailabilityStatus;
use App\Domain\Ticket\ValueObjects\PlatformSource;
use App\Domain\Ticket\ValueObjects\Price;
use App\Domain\Ticket\ValueObjects\TicketId;
use DateTimeImmutable;
use InvalidArgumentException;

use function in_array;
use function sprintf;
use function strlen;

class MonitoredTicket
{
    /** @var array<int, object> */
    private array $domainEvents = [];

    private DateTimeImmutable $lastMonitoredAt;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

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
        ?DateTimeImmutable $lastMonitoredAt = NULL,
        ?DateTimeImmutable $createdAt = NULL,
        ?DateTimeImmutable $updatedAt = NULL,
    ) {
        $this->lastMonitoredAt = $lastMonitoredAt ?? new DateTimeImmutable();
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
        $this->validate();
    }

    /**
     * Get  id
     */
    public function getId(): TicketId
    {
        return $this->id;
    }

    /**
     * Get  event id
     */
    public function getEventId(): EventId
    {
        return $this->eventId;
    }

    /**
     * Get  section
     */
    public function getSection(): string
    {
        return $this->section;
    }

    /**
     * Get  row
     */
    public function getRow(): string
    {
        return $this->row;
    }

    /**
     * Get  seat
     */
    public function getSeat(): string
    {
        return $this->seat;
    }

    /**
     * Get  price
     */
    public function getPrice(): Price
    {
        return $this->price;
    }

    /**
     * Get  availability status
     */
    public function getAvailabilityStatus(): AvailabilityStatus
    {
        return $this->availabilityStatus;
    }

    /**
     * Get  source
     */
    public function getSource(): PlatformSource
    {
        return $this->source;
    }

    /**
     * Get  description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get  last monitored at
     */
    public function getLastMonitoredAt(): DateTimeImmutable
    {
        return $this->lastMonitoredAt;
    }

    /**
     * Get  created at
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get  updated at
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * UpdatePrice
     */
    public function updatePrice(Price $newPrice): void
    {
        if (! $this->price->equals($newPrice)) {
            $oldPrice = $this->price;
            $this->price = $newPrice;
            $this->updatedAt = new DateTimeImmutable();

            $this->recordDomainEvent(
                new TicketPriceChanged(
                    $this->id,
                    $oldPrice,
                    $newPrice,
                ),
            );
        }
    }

    /**
     * UpdateAvailability
     */
    public function updateAvailability(AvailabilityStatus $newStatus): void
    {
        if (! $this->availabilityStatus->equals($newStatus)) {
            $oldStatus = $this->availabilityStatus;
            $this->availabilityStatus = $newStatus;
            $this->updatedAt = new DateTimeImmutable();

            $this->recordDomainEvent(
                new TicketAvailabilityChanged(
                    $this->id,
                    $oldStatus,
                    $newStatus,
                ),
            );
        }
    }

    /**
     * UpdateMonitoringTimestamp
     */
    public function updateMonitoringTimestamp(): void
    {
        $this->lastMonitoredAt = new DateTimeImmutable();
    }

    /**
     * Check if  available
     */
    public function isAvailable(): bool
    {
        return $this->availabilityStatus->canPurchase();
    }

    /**
     * Check if  from official source
     */
    public function isFromOfficialSource(): bool
    {
        return $this->source->isOfficial();
    }

    /**
     * Get  location description
     */
    public function getLocationDescription(): string
    {
        return trim(sprintf('%s %s %s', $this->section, $this->row, $this->seat));
    }

    /**
     * @return array<int, object>
     */
    /**
     * Get  domain events
     */
    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }

    /**
     * ClearDomainEvents
     */
    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    /**
     * Equals
     */
    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }

    /**
     * Validate
     */
    private function validate(): void
    {
        if (in_array(trim($this->section), ['', '0'], TRUE)) {
            throw new InvalidArgumentException('Section cannot be empty');
        }

        if (in_array(trim($this->row), ['', '0'], TRUE)) {
            throw new InvalidArgumentException('Row cannot be empty');
        }

        if (in_array(trim($this->seat), ['', '0'], TRUE)) {
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

    /**
     * RecordDomainEvent
     */
    private function recordDomainEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}
