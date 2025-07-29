# Ticket Platforms API Documentation

This document provides comprehensive API documentation for all integrated ticket platforms in the HDTickets system.

## Table of Contents

1. [Authentication](#authentication)
2. [Rate Limiting](#rate-limiting) 
3. [Common Response Format](#common-response-format)
4. [Platform-Specific APIs](#platform-specific-apis)
   - [FunZone API](#funzone-api)
   - [StubHub API](#stubhub-api)
   - [Viagogo API](#viagogo-api)
   - [TickPick API](#tickpick-api)
   - [Ticketmaster API](#ticketmaster-api)
5. [Error Handling](#error-handling)
6. [Webhooks](#webhooks)

## Authentication

All API endpoints require authentication using Laravel Sanctum tokens.

### Obtaining a Token

```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "role": "agent"
    }
}
```

### Using the Token

Include the token in the Authorization header for all API requests:

```http
Authorization: Bearer 1|abc123...
```

## Rate Limiting

Different rate limits apply based on the endpoint and user role:

| Endpoint Type | Rate Limit | Window |
|---------------|------------|--------|
| Authentication | 10 requests | 1 minute |
| General API | 120 requests | 1 minute |
| Scraping Endpoints | 30 requests | 1 minute |
| Import Endpoints (Agent/Admin) | 10 requests | 1 minute |

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Request limit per window
- `X-RateLimit-Remaining`: Requests remaining in current window
- `X-RateLimit-Reset`: Unix timestamp when window resets

## Common Response Format

All API responses follow a consistent format:

```json
{
    "success": true|false,
    "data": {},
    "message": "Optional message",
    "errors": {},
    "meta": {
        "pagination": {},
        "timing": {}
    }
}
```

## Platform-Specific APIs

### FunZone API

FunZone is a Slovak ticket platform specializing in entertainment events.

#### Search Events

Search for events on FunZone platform.

```http
POST /api/v1/funzone/search
```

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
    "keyword": "concert",
    "location": "Bratislava",
    "limit": 20
}
```

**Parameters:**
- `keyword` (required, string, 2-100 chars): Search keyword
- `location` (optional, string, max 100 chars): Location filter
- `limit` (optional, integer, 1-100): Maximum results to return

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "rock-koncert-bratislava-123",
            "platform": "funzone",
            "name": "Rock koncert - Bratislava",
            "date": "15.01.2025 20:00",
            "parsed_date": "2025-01-15T20:00:00",
            "venue": "Incheba Expo Arena",
            "location": "Bratislava",
            "url": "https://www.funzone.sk/event/rock-koncert-bratislava-123",
            "prices": ["€35", "€45"],
            "price_min": 35.0,
            "price_max": 45.0,
            "currency": "EUR",
            "category": "koncert",
            "ticket_count": 120,
            "scraped_at": "2025-01-22T10:30:00Z"
        }
    ],
    "meta": {
        "keyword": "concert",
        "location": "Bratislava", 
        "total_results": 1,
        "limit": 20
    }
}
```

#### Get Event Details

Get detailed information about a specific FunZone event.

```http
POST /api/v1/funzone/event-details
```

**Request Body:**
```json
{
    "url": "https://www.funzone.com/event/rock-koncert-bratislava-123"
}
```

**Parameters:**
- `url` (required, URL): Must be a valid FunZone event URL

**Response:**
```json
{
    "success": true,
    "data": {
        "id": "rock-koncert-bratislava-123",
        "platform": "funzone",
        "name": "Rock koncert - Bratislava",
        "date": "15. január 2025, 20:00",
        "parsed_date": "2025-01-15T20:00:00",
        "venue": "Incheba Expo Arena",
        "location": "Viedenská cesta 3-7, 851 01 Bratislava",
        "description": "Jedinečný rockov koncert v srdci Bratislavy...",
        "organizer": "Rock Productions s.r.o.",
        "duration": "Približne 3 hodiny s prestávkou",
        "price_categories": [
            {
                "category": "Štandardný vstup",
                "price": "€35",
                "section": "Stojisko",
                "availability": "95 lístkov"
            },
            {
                "category": "VIP vstup",
                "price": "€45", 
                "section": "Sedadlá",
                "availability": "25 lístkov"
            }
        ],
        "venue_details": {
            "name": "Incheba Expo Arena",
            "capacity": 8000,
            "amenities": ["Parkovisko", "Občerstvenie", "Bezbariérový prístup"]
        },
        "slovak_specific": {
            "region": "Bratislavský kraj",
            "venue_type": "arena",
            "cultural_category": "general"
        }
    }
}
```

#### Import Events (Agent/Admin Only)

Import FunZone events as tickets in the system.

```http
POST /api/v1/funzone/import
```

**Permissions:** Requires `agent` or `admin` role

**Request Body:**
```json
{
    "keyword": "concert",
    "location": "Bratislava",
    "limit": 10
}
```

**Response:**
```json
{
    "success": true,
    "total_found": 5,
    "imported": 3,
    "errors": [
        {
            "event": "Some Event",
            "error": "Event already exists"
        }
    ],
    "message": "Successfully imported 3 out of 5 events"
}
```

#### Import by URLs (Agent/Admin Only)

Import specific FunZone events by providing their URLs.

```http
POST /api/v1/funzone/import-urls
```

**Request Body:**
```json
{
    "urls": [
        "https://www.funzone.com/event/event1",
        "https://www.funzone.com/event/event2"
    ]
}
```

**Parameters:**
- `urls` (required, array, 1-10 items): Array of valid FunZone event URLs

#### Get Statistics

Get scraping and performance statistics for FunZone.

```http
GET /api/v1/funzone/stats
```

**Response:**
```json
{
    "success": true,
    "data": {
        "platform": "funzone",
        "total_scraped": 1250,
        "last_scrape": "2025-01-22T09:15:00Z",
        "success_rate": 92.5,
        "avg_response_time": 1120.0
    }
}
```

### StubHub API

StubHub is a major US-based ticket marketplace platform.

#### Search Events

```http
POST /api/v1/stubhub/search
```

**Request Body:**
```json
{
    "keyword": "yankees",
    "city": "New York",
    "date_start": "2025-03-01",
    "date_end": "2025-04-30",
    "limit": 25
}
```

**Parameters:**
- `keyword` (required): Event search term
- `city` (optional): City filter
- `date_start` (optional): Start date filter (YYYY-MM-DD)
- `date_end` (optional): End date filter (YYYY-MM-DD)
- `limit` (optional, 1-100): Maximum results

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "yankees-vs-red-sox-12345",
            "platform": "stubhub",
            "name": "New York Yankees vs Boston Red Sox",
            "date": "March 15, 2025 7:05 PM",
            "parsed_date": "2025-03-15T19:05:00",
            "venue": "Yankee Stadium",
            "location": "Bronx, NY",
            "url": "https://www.stubhub.com/event/yankees-vs-red-sox-12345",
            "prices": [
                {"price": 45.0, "currency": "USD", "section": "General"}
            ],
            "price_min": 45.0,
            "price_max": 125.0,
            "ticket_count": 1250
        }
    ]
}
```

#### Get Event Details

```http
POST /api/v1/stubhub/event-details
```

**Request Body:**
```json
{
    "url": "https://www.stubhub.com/event/yankees-vs-red-sox-12345"
}
```

### Viagogo API

Viagogo is a global ticket marketplace.

#### Search Events  

```http
POST /api/v1/viagogo/search
```

**Request Body:**
```json
{
    "keyword": "ed sheeran",
    "city": "London",
    "limit": 20
}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "ed-sheeran-london-123",
            "platform": "viagogo", 
            "name": "Ed Sheeran - London",
            "date": "2025-05-15 19:30",
            "venue": "The O2 Arena",
            "location": "London",
            "currency": "GBP",
            "price_from": 89.0,
            "ticket_count": "500+"
        }
    ]
}
```

### TickPick API

TickPick is a no-fee ticket marketplace.

#### Search Events

```http  
POST /api/v1/tickpick/search
```

**Request Body:**
```json
{
    "keyword": "the weeknd",
    "city": "New York",
    "limit": 15
}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "the-weeknd-after-hours-tour",
            "platform": "tickpick",
            "name": "The Weeknd - After Hours Tour", 
            "date": "Jun 10, 2025 8:00 PM",
            "venue": "Madison Square Garden",
            "location": "New York, NY",
            "price_range": "$89 - $399",
            "no_fees": true,
            "listing_count": 450
        }
    ]
}
```

### Ticketmaster API

Ticketmaster is the largest ticket sales platform.

#### Search Events

```http
POST /api/v1/ticketmaster/search
```

**Request Body:**
```json
{
    "keyword": "taylor swift",
    "city": "Los Angeles", 
    "classification": "music",
    "limit": 30
}
```

## Error Handling

### Error Response Format

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Specific error message"]
    },
    "error_code": "PLATFORM_ERROR"
}
```

### Common Error Codes

- `VALIDATION_ERROR` (422): Request validation failed
- `AUTHENTICATION_ERROR` (401): Invalid or missing authentication
- `AUTHORIZATION_ERROR` (403): Insufficient permissions
- `RATE_LIMIT_ERROR` (429): Rate limit exceeded
- `PLATFORM_ERROR` (500): Platform-specific error
- `SCRAPING_DETECTED` (503): Bot detection triggered
- `TIMEOUT_ERROR` (504): Request timeout

### Platform-Specific Errors

#### FunZone Errors
- Slovak language content parsing issues
- Currency conversion (EUR) problems
- Regional venue detection failures

#### StubHub Errors  
- API authentication failures (when API keys provided)
- Fallback scraping blocked by Cloudflare
- Price format parsing issues (USD)

#### Viagogo Errors
- Multi-currency price handling
- Geographic restrictions
- Dynamic pricing changes

## Webhooks

Configure webhooks to receive real-time notifications about platform events.

### Webhook Events

- `platform.scraping.completed`: Scraping operation completed
- `platform.scraping.failed`: Scraping operation failed
- `platform.event.imported`: Event successfully imported
- `platform.rate_limit.exceeded`: Rate limit exceeded

### Webhook Payload

```json
{
    "event": "platform.scraping.completed",
    "platform": "funzone",
    "timestamp": "2025-01-22T10:30:00Z",
    "data": {
        "keyword": "concert",
        "results_count": 25,
        "duration_ms": 1240
    }
}
```

## SDK and Code Examples

### PHP/Laravel Example

```php
use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'Authorization' => 'Bearer ' . $token,
    'Content-Type' => 'application/json',
])
->post('https://api.hdtickets.local/api/v1/funzone/search', [
    'keyword' => 'concert',
    'location' => 'Bratislava',
    'limit' => 20
]);

$events = $response->json('data');
```

### JavaScript Example

```javascript
const response = await fetch('/api/v1/stubhub/search', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        keyword: 'yankees',
        city: 'New York',
        limit: 25
    })
});

const result = await response.json();
```

### Python Example

```python
import requests

headers = {
    'Authorization': f'Bearer {token}',
    'Content-Type': 'application/json'
}

data = {
    'keyword': 'ed sheeran',
    'city': 'London',
    'limit': 20
}

response = requests.post(
    'https://api.hdtickets.local/api/v1/viagogo/search',
    headers=headers,
    json=data
)

events = response.json()['data']
```

## Testing

API endpoints can be tested using the provided Postman collection or any HTTP client.

### Test Credentials

Development environment test credentials:
- Email: `test@hdtickets.local`
- Password: `password`
- Role: `agent`

### Mock Responses

For testing purposes, mock responses are available when using the query parameter `mock=true`:

```http
POST /api/v1/funzone/search?mock=true
```

This will return consistent mock data without making actual scraping requests.
