<?php

namespace App\Services\TicketApis;

class TicketmasterClient extends BaseApiClient
{
    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'User-Agent' => 'Laravel Ticker Manager/1.0'
        ];
    }

    public function searchEvents(array $criteria): array
    {
        return $this->makeRequest('GET', 'events', $criteria);
    }

    public function getEvent(string $eventId): array
    {
        return $this->makeRequest('GET', "events/{$eventId}");
    }

    public function getVenue(string $venueId): array
    {
        return $this->makeRequest('GET', "venues/{$venueId}");
    }

    protected function transformEventData(array $eventData): array
    {
        return [
            'id' => $eventData['id'] ?? null,
            'name' => $eventData['name'] ?? 'Unnamed Event',
            'date' => $eventData['dates']['start']['localDate'] ?? null,
            'time' => $eventData['dates']['start']['localTime'] ?? null,
            'status' => $eventData['dates']['status']['code'] ?? 'unknown',
            'venue' => $eventData['_embedded']['venues'][0]['name'] ?? 'Unknown Venue',
            'city' => $eventData['_embedded']['venues'][0]['city']['name'] ?? 'Unknown City',
            'country' => $eventData['_embedded']['venues'][0]['country']['name'] ?? 'Unknown Country',
            'url' => $eventData['url'] ?? '',
        ];
    }
}
