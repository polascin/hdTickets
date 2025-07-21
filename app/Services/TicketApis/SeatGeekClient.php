<?php

namespace App\Services\TicketApis;

class SeatGeekClient extends BaseApiClient
{
    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->config['client_id'] . ':' . $this->config['client_secret']),
        ];
    }

    public function searchEvents(array $criteria): array
    {
        $params = $this->buildSearchParams($criteria);
        return $this->makeRequest('GET', 'events', $params);
    }

    public function getEvent(string $eventId): array
    {
        return $this->makeRequest('GET', "events/{$eventId}");
    }

    public function getVenue(string $venueId): array
    {
        return $this->makeRequest('GET', "venues/{$venueId}");
    }

    protected function buildSearchParams(array $criteria): array
    {
        $params = [];

        if (isset($criteria['q'])) {
            $params['q'] = $criteria['q'];
        }

        if (isset($criteria['datetime_utc.gte'])) {
            $params['datetime_utc.gte'] = $criteria['datetime_utc.gte'];
        }

        if (isset($criteria['datetime_utc.lte'])) {
            $params['datetime_utc.lte'] = $criteria['datetime_utc.lte'];
        }

        if (isset($criteria['venue.city'])) {
            $params['venue.city'] = $criteria['venue.city'];
        }

        if (isset($criteria['venue.state'])) {
            $params['venue.state'] = $criteria['venue.state'];
        }

        if (isset($criteria['taxonomies.name'])) {
            $params['taxonomies.name'] = $criteria['taxonomies.name'];
        }

        if (isset($criteria['per_page'])) {
            $params['per_page'] = min(100, $criteria['per_page']);
        }

        return $params;
    }

    protected function transformEventData(array $eventData): array
    {
        $lowestPrice = null;
        $highestPrice = null;

        if (isset($eventData['stats']['lowest_price'])) {
            $lowestPrice = $eventData['stats']['lowest_price'];
        }

        if (isset($eventData['stats']['highest_price'])) {
            $highestPrice = $eventData['stats']['highest_price'];
        }

        return [
            'id' => $eventData['id'] ?? null,
            'name' => $eventData['title'] ?? 'Unnamed Event',
            'date' => isset($eventData['datetime_local']) ? date('Y-m-d', strtotime($eventData['datetime_local'])) : null,
            'time' => isset($eventData['datetime_local']) ? date('H:i:s', strtotime($eventData['datetime_local'])) : null,
            'status' => $eventData['announce_date'] ? 'onsale' : 'unknown',
            'venue' => $eventData['venue']['name'] ?? 'Unknown Venue',
            'city' => $eventData['venue']['city'] ?? 'Unknown City',
            'country' => $eventData['venue']['country'] ?? 'Unknown Country',
            'url' => $eventData['url'] ?? '',
            'price_min' => $lowestPrice,
            'price_max' => $highestPrice,
            'ticket_count' => $eventData['stats']['listing_count'] ?? null,
        ];
    }

    /**
     * Get available tickets for an event
     */
    public function getEventTickets(string $eventId, array $filters = []): array
    {
        $params = array_merge(['event_id' => $eventId], $filters);
        return $this->makeRequest('GET', 'listings', $params);
    }
}
