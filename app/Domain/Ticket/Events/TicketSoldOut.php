<?php declare(strict_types=1);

namespace App\Domain\Ticket\Events;

use App\Domain\Shared\Events\AbstractDomainEvent;
use App\Domain\Ticket\ValueObjects\PlatformSource;
use App\Domain\Ticket\ValueObjects\TicketId;
use DateTimeImmutable;

final class TicketSoldOut extends AbstractDomainEvent
{
    public function __construct(
        public TicketId $ticketId,
        public PlatformSource $platformSource,
        public DateTimeImmutable $soldOutAt,
        public int $durationOnSaleMinutes,
        /** @var array<string, mixed> */
        public array $finalPriceData = [],
        /** @var array<string, mixed> */
        array $metadata = [],
    ) {
        parent::__construct($metadata);
    }

    public function getAggregateRootId(): string
    {
        return $this->ticketId->value();
    }

    public function getAggregateType(): string
    {
        return 'ticket';
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return [
            'ticket_id'                => $this->ticketId->value(),
            'platform_source'          => $this->platformSource->platform(),
            'sold_out_at'              => $this->soldOutAt->format('Y-m-d H:i:s'),
            'duration_on_sale_minutes' => $this->durationOnSaleMinutes,
            'final_price_data'         => $this->finalPriceData,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function populateFromPayload(array $payload): void
    {
        $this->ticketId = new TicketId($payload['ticket_id']);
        $this->platformSource = new PlatformSource($payload['platform_source']);
        $this->soldOutAt = new DateTimeImmutable($payload['sold_out_at']);
        $this->durationOnSaleMinutes = $payload['duration_on_sale_minutes'];
        $this->finalPriceData = $payload['final_price_data'];
    }
}
