<?php declare(strict_types=1);

namespace App\Infrastructure\External;

use App\Domain\Event\ValueObjects\EventDate;
use App\Domain\Event\ValueObjects\SportCategory;
use App\Domain\Ticket\ValueObjects\AvailabilityStatus;
use App\Domain\Ticket\ValueObjects\PlatformSource;
use App\Domain\Ticket\ValueObjects\Price;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

class TicketmasterAntiCorruptionLayer
{
    /**
     * Convert Ticketmaster API response to domain objects
     */
    /**
     * AdaptEventData
     */
    public function adaptEventData(array $ticketmasterData): array
    {
        $adaptedEvents = [];

        foreach ($ticketmasterData['_embedded']['events'] ?? [] as $event) {
            $adaptedEvents[] = $this->adaptSingleEvent($event);
        }

        return $adaptedEvents;
    }

    /**
     * Adapt ticket-specific data from Ticketmaster
     */
    /**
     * AdaptTicketData
     */
    public function adaptTicketData(array $ticketData): array
    {
        return [
            'section'      => $ticketData['seatMap']['section'] ?? 'General',
            'row'          => $ticketData['seatMap']['row'] ?? '',
            'seat'         => $ticketData['seatMap']['seat'] ?? '',
            'price'        => $this->adaptTicketPrice($ticketData),
            'availability' => $this->adaptTicketAvailability($ticketData),
        ];
    }

    /**
     * AdaptSingleEvent
     */
    private function adaptSingleEvent(array $eventData): array
    {
        return [
            'external_id'  => $this->extractExternalId($eventData),
            'name'         => $this->extractEventName($eventData),
            'category'     => $this->adaptSportCategory($eventData),
            'date'         => $this->adaptEventDate($eventData),
            'venue'        => $this->adaptVenueData($eventData),
            'price_range'  => $this->adaptPriceRange($eventData),
            'availability' => $this->adaptAvailabilityStatus($eventData),
            'source'       => $this->createPlatformSource($eventData),
        ];
    }

    /**
     * ExtractExternalId
     */
    private function extractExternalId(array $eventData): string
    {
        return $eventData['id'] ?? uniqid('tm_');
    }

    /**
     * ExtractEventName
     */
    private function extractEventName(array $eventData): string
    {
        return $eventData['name'] ?? 'Unknown Event';
    }

    /**
     * AdaptSportCategory
     */
    private function adaptSportCategory(array $eventData): SportCategory
    {
        $segment = $eventData['classifications'][0]['segment']['name'] ?? 'OTHER';
        $genre = $eventData['classifications'][0]['genre']['name'] ?? '';

        // Map Ticketmaster categories to our domain categories
        $categoryMapping = [
            'Sports'         => $this->mapSportGenre($genre),
            'Music'          => 'OTHER', // Not sports
            'Arts & Theatre' => 'OTHER',
            'Miscellaneous'  => 'OTHER',
        ];

        $mappedCategory = $categoryMapping[$segment] ?? 'OTHER';

        try {
            return new SportCategory($mappedCategory);
        } catch (InvalidArgumentException $e) {
            return new SportCategory('OTHER');
        }
    }

    /**
     * MapSportGenre
     */
    private function mapSportGenre(string $genre): string
    {
        $genreMapping = [
            'Football'          => 'FOOTBALL',
            'Soccer'            => 'FOOTBALL',
            'Basketball'        => 'BASKETBALL',
            'Tennis'            => 'TENNIS',
            'Cricket'           => 'CRICKET',
            'Rugby'             => 'RUGBY',
            'Baseball'          => 'BASEBALL',
            'American Football' => 'AMERICAN_FOOTBALL',
            'Ice Hockey'        => 'ICE_HOCKEY',
            'Hockey'            => 'ICE_HOCKEY',
            'Golf'              => 'GOLF',
            'Motor Racing'      => 'MOTORSPORT',
            'Formula 1'         => 'MOTORSPORT',
            'Boxing'            => 'BOXING',
            'MMA'               => 'MMA',
            'Cycling'           => 'CYCLING',
            'Athletics'         => 'ATHLETICS',
            'Swimming'          => 'SWIMMING',
            'Gymnastics'        => 'GYMNASTICS',
        ];

        return $genreMapping[$genre] ?? 'OTHER';
    }

    /**
     * AdaptEventDate
     */
    private function adaptEventDate(array $eventData): EventDate
    {
        $dateString = $eventData['dates']['start']['dateTime'] ??
                     $eventData['dates']['start']['localDate'] ??
                     date('c', strtotime('+1 day'));

        try {
            return new EventDate(new DateTimeImmutable($dateString));
        } catch (Exception $e) {
            // Fallback to tomorrow if date parsing fails
            return new EventDate(new DateTimeImmutable('+1 day'));
        }
    }

    /**
     * AdaptVenueData
     */
    private function adaptVenueData(array $eventData): array
    {
        $venue = $eventData['_embedded']['venues'][0] ?? [];

        return [
            'name'     => $venue['name'] ?? 'Unknown Venue',
            'city'     => $venue['city']['name'] ?? 'Unknown City',
            'country'  => $venue['country']['name'] ?? 'Unknown Country',
            'address'  => $this->buildAddress($venue),
            'capacity' => $this->extractCapacity($venue),
        ];
    }

    /**
     * BuildAddress
     */
    private function buildAddress(array $venue): ?string
    {
        $addressParts = [];

        if (!empty($venue['address']['line1'])) {
            $addressParts[] = $venue['address']['line1'];
        }

        if (!empty($venue['address']['line2'])) {
            $addressParts[] = $venue['address']['line2'];
        }

        if (!empty($venue['postalCode'])) {
            $addressParts[] = $venue['postalCode'];
        }

        return !empty($addressParts) ? implode(', ', $addressParts) : NULL;
    }

    /**
     * ExtractCapacity
     */
    private function extractCapacity(array $venue): ?int
    {
        if (isset($venue['generalInfo']['childRule'])) {
            // This is a hack - Ticketmaster doesn't always provide capacity
            // You might need to maintain a separate venue capacity database
            return NULL;
        }

        return $venue['capacity'] ?? NULL;
    }

    /**
     * AdaptPriceRange
     */
    private function adaptPriceRange(array $eventData): array
    {
        $priceRanges = $eventData['priceRanges'] ?? [];
        $prices = [];

        foreach ($priceRanges as $priceRange) {
            $currency = $priceRange['currency'] ?? 'GBP';

            if (isset($priceRange['min'])) {
                $prices['min'] = new Price($priceRange['min'], $currency);
            }

            if (isset($priceRange['max'])) {
                $prices['max'] = new Price($priceRange['max'], $currency);
            }
        }

        return $prices;
    }

    /**
     * AdaptAvailabilityStatus
     */
    private function adaptAvailabilityStatus(array $eventData): AvailabilityStatus
    {
        $salesStatus = $eventData['dates']['status']['code'] ?? 'unknown';

        $statusMapping = [
            'onsale'      => AvailabilityStatus::AVAILABLE,
            'presale'     => AvailabilityStatus::ON_SALE_SOON,
            'offsale'     => AvailabilityStatus::SOLD_OUT,
            'cancelled'   => AvailabilityStatus::SOLD_OUT,
            'postponed'   => AvailabilityStatus::UNKNOWN,
            'rescheduled' => AvailabilityStatus::UNKNOWN,
        ];

        $mappedStatus = $statusMapping[strtolower($salesStatus)] ?? AvailabilityStatus::UNKNOWN;

        return new AvailabilityStatus($mappedStatus);
    }

    /**
     * CreatePlatformSource
     */
    private function createPlatformSource(array $eventData): PlatformSource
    {
        $url = $eventData['url'] ?? NULL;

        return new PlatformSource('TICKETMASTER', $url);
    }

    /**
     * AdaptTicketPrice
     */
    private function adaptTicketPrice(array $ticketData): Price
    {
        $priceValue = $ticketData['pricing']['total'] ??
                     $ticketData['pricing']['face'] ??
                     0.00;

        $currency = $ticketData['pricing']['currency'] ?? 'GBP';

        return new Price((float) $priceValue, $currency);
    }

    /**
     * AdaptTicketAvailability
     */
    private function adaptTicketAvailability(array $ticketData): AvailabilityStatus
    {
        $available = $ticketData['available'] ?? TRUE;

        if (!$available) {
            return new AvailabilityStatus(AvailabilityStatus::SOLD_OUT);
        }

        $inventory = $ticketData['inventory'] ?? NULL;

        if ($inventory !== NULL && $inventory < 10) {
            return new AvailabilityStatus(AvailabilityStatus::LIMITED);
        }

        return new AvailabilityStatus(AvailabilityStatus::AVAILABLE);
    }
}
