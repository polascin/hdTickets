<?php declare(strict_types=1);

namespace App\Domain\Ticket\Events;

use App\Domain\Shared\Events\AbstractDomainEvent;
use App\Domain\Ticket\ValueObjects\PlatformSource;
use App\Domain\Ticket\ValueObjects\Price;
use App\Domain\Ticket\ValueObjects\TicketId;
use DateTimeImmutable;

final class TicketDiscovered extends AbstractDomainEvent
{
    public function __construct(
        public TicketId $ticketId,
        public string $eventName,
        public string $eventCategory,
        public string $venue,
        public DateTimeImmutable $eventDate,
        public Price $price,
        public PlatformSource $platformSource,
        public int $availableQuantity,
        /** @var array<string, mixed> Array containing detailed ticket information like seating section, row, etc. */
        public array $ticketDetails = [],
        /** @var array<string, mixed> */
        array $metadata = [],
    ) {
        parent::__construct($metadata);
    }

    /**
     * Get  aggregate root id
     */
    public function getAggregateRootId(): string
    {
        return $this->ticketId->value();
    }

    /**
     * Get  aggregate type
     */
    public function getAggregateType(): string
    {
        return 'ticket';
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * Get  payload
     */
    public function getPayload(): array
    {
        return [
            'ticket_id'      => $this->ticketId->value(),
            'event_name'     => $this->eventName,
            'event_category' => $this->eventCategory,
            'venue'          => $this->venue,
            'event_date'     => $this->eventDate->format('Y-m-d H:i:s'),
            'price'          => [
                'amount'   => $this->price->amount(),
                'currency' => $this->price->currency(),
            ],
            'platform_source'    => $this->platformSource->platform(),
            'available_quantity' => $this->availableQuantity,
            'ticket_details'     => $this->ticketDetails,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    /**
     * PopulateFromPayload
     */
    protected function populateFromPayload(array $payload): void
    {
        $this->ticketId = new TicketId($payload['ticket_id']);
        $this->eventName = $payload['event_name'];
        $this->eventCategory = $payload['event_category'];
        $this->venue = $payload['venue'];
        $this->eventDate = new DateTimeImmutable($payload['event_date']);
        $this->price = new Price($payload['price']['amount'], $payload['price']['currency']);
        $this->platformSource = new PlatformSource($payload['platform_source']);
        $this->availableQuantity = $payload['available_quantity'];
        $this->ticketDetails = $payload['ticket_details'];
    }
}
