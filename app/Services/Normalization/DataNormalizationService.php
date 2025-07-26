<?php

namespace App\Services\Normalization;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DataNormalizationService
{
    /**
     * Platform-specific currency mappings
     */
    protected array $platformCurrencies = [
        'viagogo' => 'USD',
        'stubhub' => 'USD',
        'ticketmaster' => 'USD',
        'tickpick' => 'USD',
        'seatgeek' => 'USD',
    ];

    /**
     * Status mapping for consistency across platforms
     */
    protected array $statusMapping = [
        'onsale' => 'available',
        'available' => 'available',
        'dostupné' => 'available',
        'soldout' => 'sold_out',
        'vypredané' => 'sold_out',
        'sold out' => 'sold_out',
        'presale' => 'presale',
        'predpredaj' => 'presale',
        'cancelled' => 'cancelled',
        'zrušené' => 'cancelled',
        'postponed' => 'postponed',
        'odložené' => 'postponed',
        'unknown' => 'unknown',
    ];

    /**
     * Normalize event data across platforms with comprehensive data transformation.
     */
    public function normalize(array $eventData): array
    {
        try {
            $platform = $eventData['platform'] ?? 'unknown';
            
            return [
                // Core identification
                'id' => $this->normalizeId($eventData),
                'platform' => $platform,
                'external_id' => $eventData['id'] ?? null,
                'url' => $eventData['url'] ?? null,
                
                // Event information
                'name' => $this->normalizeName($eventData),
                'description' => $this->normalizeDescription($eventData),
                
                // Date and time
                'date' => $this->normalizeDate($eventData),
                'time' => $this->normalizeTime($eventData),
                'timezone' => $this->normalizeTimezone($eventData, $platform),
                
                // Location information
                'venue' => $this->normalizeVenue($eventData),
                'venue_address' => $eventData['address'] ?? $eventData['location'] ?? null,
                'city' => $this->normalizeCity($eventData),
                'country' => $this->normalizeCountry($eventData, $platform),
                
                // Pricing
                'price_min' => $this->normalizePrice($eventData['price_min'] ?? null),
                'price_max' => $this->normalizePrice($eventData['price_max'] ?? null),
                'currency' => $this->normalizeCurrency($eventData, $platform),
                'prices' => $this->normalizePrices($eventData),
                
                // Availability and status
                'availability_status' => $this->normalizeStatus($eventData),
                'ticket_count' => $this->normalizeTicketCount($eventData),
                
                // Additional metadata
                'category' => $this->normalizeCategory($eventData),
                'image_url' => $eventData['image_url'] ?? $eventData['image'] ?? null,
                'organizer' => $eventData['organizer'] ?? null,
                
                // Metadata
                'scraped_at' => $eventData['scraped_at'] ?? now()->toISOString(),
                'normalized_at' => now()->toISOString(),
                'platform_specific' => $this->extractPlatformSpecific($eventData, $platform),
                'raw_data' => $eventData,
            ];
        } catch (\Exception $e) {
            Log::error('Event normalization failed', [
                'error' => $e->getMessage(),
                'event_data' => $eventData
            ]);
            return [];
        }
    }

    /**
     * Normalize multiple events at once.
     */
    public function normalizeMultiple(array $eventsData): array
    {
        return array_filter(array_map([$this, 'normalize'], $eventsData));
    }

    /**
     * Normalize event ID to ensure uniqueness across platforms.
     */
    protected function normalizeId(array $eventData): ?string
    {
        $platform = $eventData['platform'] ?? 'unknown';
        $id = $eventData['id'] ?? $eventData['external_id'] ?? null;
        
        if (!$id) {
            return null;
        }
        
        return $platform . '_' . $id;
    }

    /**
     * Normalize event name.
     */
    protected function normalizeName(array $eventData): string
    {
        $name = $eventData['name'] ?? 'Unnamed Event';
        
        // Clean up common platform prefixes/suffixes
        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name);
        
        return $name;
    }

    /**
     * Normalize event description.
     */
    protected function normalizeDescription(array $eventData): ?string
    {
        return $eventData['description'] ?? $eventData['description_snippet'] ?? null;
    }

    /**
     * Normalize event date.
     */
    protected function normalizeDate(array $eventData): ?string
    {
        if (isset($eventData['parsed_date'])) {
            if ($eventData['parsed_date'] instanceof \DateTime) {
                return $eventData['parsed_date']->format('Y-m-d');
            }
        }
        
        if (isset($eventData['date'])) {
            try {
                $date = new \DateTime($eventData['date']);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }

    /**
     * Normalize event time.
     */
    protected function normalizeTime(array $eventData): ?string
    {
        if (isset($eventData['parsed_date'])) {
            if ($eventData['parsed_date'] instanceof \DateTime) {
                return $eventData['parsed_date']->format('H:i:s');
            }
        }
        
        return $eventData['time'] ?? null;
    }

    /**
     * Normalize timezone based on platform and location.
     */
    protected function normalizeTimezone(array $eventData, string $platform): string
    {
        // Default timezone mapping by platform
        $platformTimezones = [
            'viagogo' => 'UTC',
            'stubhub' => 'America/New_York',
            'ticketmaster' => 'America/New_York',
            'tickpick' => 'America/New_York',
        ];
        
        return $eventData['timezone'] ?? $platformTimezones[$platform] ?? 'UTC';
    }

    /**
     * Normalize venue name.
     */
    protected function normalizeVenue(array $eventData): string
    {
        return trim($eventData['venue'] ?? 'Unknown Venue');
    }

    /**
     * Normalize city name.
     */
    protected function normalizeCity(array $eventData): string
    {
        return trim($eventData['city'] ?? 'Unknown City');
    }

    /**
     * Normalize country based on platform defaults.
     */
    protected function normalizeCountry(array $eventData, string $platform): string
    {
        if (isset($eventData['country']) && !empty($eventData['country'])) {
            return $eventData['country'];
        }
        
        // Platform default countries
        $platformCountries = [
            'viagogo' => 'United States',
            'stubhub' => 'United States',
            'ticketmaster' => 'United States',
            'tickpick' => 'United States',
        ];
        
        return $platformCountries[$platform] ?? 'Unknown Country';
    }

    /**
     * Normalize price to float.
     */
    protected function normalizePrice($price): ?float
    {
        if (is_null($price)) {
            return null;
        }
        
        if (is_numeric($price)) {
            return floatval($price);
        }
        
        // Extract numeric value from string
        $priceString = str_replace([',', ' '], '', $price);
        if (preg_match('/([0-9]+\.?[0-9]*)/', $priceString, $matches)) {
            return floatval($matches[1]);
        }
        
        return null;
    }

    /**
     * Normalize currency.
     */
    protected function normalizeCurrency(array $eventData, string $platform): string
    {
        if (isset($eventData['currency']) && !empty($eventData['currency'])) {
            return strtoupper($eventData['currency']);
        }
        
        return $this->platformCurrencies[$platform] ?? 'USD';
    }

    /**
     * Normalize prices array.
     */
    protected function normalizePrices(array $eventData): array
    {
        $prices = $eventData['prices'] ?? [];
        $normalizedPrices = [];
        
        foreach ($prices as $price) {
            if (is_array($price)) {
                $normalizedPrices[] = [
                    'price' => $this->normalizePrice($price['price'] ?? null),
                    'currency' => strtoupper($price['currency'] ?? $this->normalizeCurrency($eventData, $eventData['platform'] ?? 'unknown')),
                    'section' => $price['section'] ?? 'General',
                ];
            } else {
                $normalizedPrices[] = [
                    'price' => $this->normalizePrice($price),
                    'currency' => $this->normalizeCurrency($eventData, $eventData['platform'] ?? 'unknown'),
                    'section' => 'General',
                ];
            }
        }
        
        return $normalizedPrices;
    }

    /**
     * Normalize availability status.
     */
    protected function normalizeStatus(array $eventData): string
    {
        $status = strtolower($eventData['availability_status'] ?? $eventData['status'] ?? 'unknown');
        
        return $this->statusMapping[$status] ?? 'unknown';
    }

    /**
     * Normalize ticket count.
     */
    protected function normalizeTicketCount(array $eventData): ?int
    {
        $count = $eventData['ticket_count'] ?? $eventData['available_listings'] ?? null;
        
        return is_numeric($count) ? intval($count) : null;
    }

    /**
     * Normalize event category.
     */
    protected function normalizeCategory(array $eventData): ?string
    {
        return $eventData['category'] ?? $eventData['entertainment_category'] ?? null;
    }

    /**
     * Extract platform-specific data.
     */
    protected function extractPlatformSpecific(array $eventData, string $platform): array
    {
        $platformSpecific = [];
        
        switch ($platform) {
            case 'stubhub':
                $platformSpecific = [
                    'ticket_classes' => $eventData['ticket_classes'] ?? [],
                    'zones' => $eventData['zones'] ?? [],
                    'section_mappings' => $eventData['section_mappings'] ?? [],
                ];
                break;
                
            case 'ticketmaster':
                $platformSpecific = [
                    'tm_event_id' => $eventData['id'] ?? null,
                    'presale_info' => $eventData['presale_info'] ?? null,
                    'verified_resale' => $eventData['verified_resale'] ?? false,
                ];
                break;
        }
        
        return array_filter($platformSpecific);
    }

    /**
     * Validate normalized event data.
     */
    public function validate(array $normalizedEvent): bool
    {
        $required = ['platform', 'name', 'currency'];
        
        foreach ($required as $field) {
            if (!isset($normalizedEvent[$field]) || empty($normalizedEvent[$field])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get field mapping for platform.
     */
    public function getPlatformFieldMapping(string $platform): array
    {
        $mappings = [
            'stubhub' => [
                'event_id' => 'id',
                'event_name' => 'name',
                'event_date' => 'parsed_date',
                'event_venue' => 'venue',
                'event_location' => 'city',
                'price_range' => 'prices',
                'listings_count' => 'available_listings',
            ],
            'ticketmaster' => [
                'event_id' => 'id',
                'event_name' => 'name',
                'event_date' => 'parsed_date',
                'event_venue' => 'venue',
                'event_location' => 'city',
                'price_range' => 'prices',
                'event_status' => 'status',
            ],
        ];
        
        return $mappings[$platform] ?? [];
    }

    /**
     * Compare events across platforms for deduplication.
     */
    public function compareEvents(array $event1, array $event2): float
    {
        $similarity = 0;
        $totalFields = 0;
        
        // Compare event names
        if (isset($event1['name']) && isset($event2['name'])) {
            $similarity += $this->stringSimilarity($event1['name'], $event2['name']);
            $totalFields++;
        }
        
        // Compare venues
        if (isset($event1['venue']) && isset($event2['venue'])) {
            $similarity += $this->stringSimilarity($event1['venue'], $event2['venue']);
            $totalFields++;
        }
        
        // Compare dates
        if (isset($event1['date']) && isset($event2['date'])) {
            $similarity += ($event1['date'] === $event2['date']) ? 1 : 0;
            $totalFields++;
        }
        
        // Compare cities
        if (isset($event1['city']) && isset($event2['city'])) {
            $similarity += $this->stringSimilarity($event1['city'], $event2['city']);
            $totalFields++;
        }
        
        return $totalFields > 0 ? $similarity / $totalFields : 0;
    }

    /**
     * Calculate string similarity.
     */
    private function stringSimilarity(string $str1, string $str2): float
    {
        return similar_text(strtolower($str1), strtolower($str2)) / max(strlen($str1), strlen($str2));
    }
}
