# Real-Time Sports Ticket Statistics Components

This directory contains five specialized blade components for displaying real-time sports ticket statistics in the HD Tickets dashboard. Each component follows the established design patterns and includes proper loading states.

## Components Overview

### 1. Live Ticker (`live-ticker.blade.php`)
Displays a scrolling ticker of new ticket announcements.

**Props:**
- `tickets` (array) - Array of ticket data
- `isLoading` (boolean, default: false) - Loading state
- `speed` (int, default: 50) - Scrolling speed in pixels per second
- `height` (string, default: '60px') - Ticker height

**Example Usage:**
```php
<x-dashboard.live-ticker 
    :tickets="[
        ['event' => 'Lakers vs Warriors', 'venue' => 'Staples Center', 'price' => 150, 'availability' => 'Available'],
        ['event' => 'Yankees vs Red Sox', 'venue' => 'Yankee Stadium', 'price' => 85, 'availability' => 'Limited']
    ]" 
    :isLoading="false"
    height="80px"
/>
```

### 2. Event Spotlight (`event-spotlight.blade.php`)
Features high-demand events in a rotating carousel format.

**Props:**
- `events` (array) - Array of featured events
- `isLoading` (boolean, default: false) - Loading state
- `title` (string, default: 'High-Demand Events') - Widget title
- `autoRotate` (boolean, default: true) - Auto-rotation enabled
- `interval` (int, default: 5000) - Rotation interval in milliseconds

**Example Usage:**
```php
<x-dashboard.event-spotlight 
    :events="[
        [
            'name' => 'Super Bowl LVIII',
            'venue' => 'Allegiant Stadium',
            'date' => '2024-02-11',
            'demand_level' => 'very_high',
            'price_range' => ['min' => 2500, 'max' => 15000],
            'availability' => 15
        ]
    ]"
    :autoRotate="true"
    interval="3000"
/>
```

### 3. Price Tracker (`price-tracker.blade.php`)
Real-time price monitoring widget with mini charts and trend indicators.

**Props:**
- `events` (array) - Array of events with price data
- `isLoading` (boolean, default: false) - Loading state
- `title` (string, default: 'Price Tracker') - Widget title
- `updateInterval` (int, default: 30000) - Auto-refresh interval in milliseconds
- `showChart` (boolean, default: true) - Show price history charts
- `maxEntries` (int, default: 5) - Maximum events to display

**Example Usage:**
```php
<x-dashboard.price-tracker 
    :events="[
        [
            'name' => 'Lakers vs Warriors',
            'venue' => 'Staples Center',
            'sport' => 'basketball',
            'date' => '2024-01-15',
            'current_price' => 150,
            'previous_price' => 140,
            'price_change' => 7.1,
            'trend' => 'up',
            'price_history' => [120, 125, 140, 145, 150],
            'last_updated' => '2024-01-10 14:30:00'
        ]
    ]"
    :showChart="true"
    updateInterval="60000"
/>
```

### 4. Availability Map (`availability-map.blade.php`)
Visual stadium seat availability with interactive sections.

**Props:**
- `venue` (array) - Venue information
- `sections` (array) - Array of stadium sections with availability data
- `isLoading` (boolean, default: false) - Loading state
- `title` (string, default: 'Seat Availability') - Widget title
- `showLegend` (boolean, default: true) - Show availability legend
- `interactive` (boolean, default: true) - Enable section interactions

**Example Usage:**
```php
<x-dashboard.availability-map 
    :venue="['name' => 'Staples Center', 'capacity' => '20,000']"
    :sections="[
        ['id' => 'section-1', 'name' => 'Lower Bowl A', 'availability' => 85, 'price' => 150],
        ['id' => 'section-2', 'name' => 'Upper Deck B', 'availability' => 45, 'price' => 80],
        ['id' => 'section-3', 'name' => 'Premium Box', 'availability' => 10, 'price' => 500]
    ]"
    :interactive="true"
/>
```

### 5. Trending Events (`trending-events.blade.php`)
Popular upcoming sports events with trending indicators and metrics.

**Props:**
- `events` (array) - Array of trending events
- `isLoading` (boolean, default: false) - Loading state
- `title` (string, default: 'Trending Sports Events') - Widget title
- `maxEvents` (int, default: 6) - Maximum events to show
- `showViewAll` (boolean, default: true) - Show "View All" button
- `timeframe` (string, default: '7d') - Trending timeframe (24h, 7d, 30d)

**Example Usage:**
```php
<x-dashboard.trending-events 
    :events="[
        [
            'name' => 'Lakers vs Warriors',
            'venue' => 'Staples Center',
            'sport' => 'basketball',
            'date' => '2024-01-15',
            'views_increase' => 45,
            'search_volume' => 12500,
            'ticket_interest' => 78,
            'social_mentions' => 2300,
            'price_range' => ['min' => 85, 'max' => 350],
            'price_trend' => 'up',
            'trending_reasons' => ['Playoff Race', 'Rivalry Game']
        ]
    ]"
    maxEvents="8"
    timeframe="24h"
/>
```

## Data Structure Examples

### Event Data Structure
```php
[
    'id' => 'unique_event_id',
    'name' => 'Event Name',
    'venue' => 'Venue Name',
    'sport' => 'basketball|football|baseball|soccer|hockey|tennis',
    'date' => '2024-01-15 19:30:00',
    'image' => 'path/to/image.jpg', // optional
    'price_range' => [
        'min' => 50,
        'max' => 500
    ],
    'availability' => 75, // percentage
    'demand_level' => 'low|medium|high|very_high',
    // Additional fields specific to each component...
]
```

### Section Data Structure (for Availability Map)
```php
[
    'id' => 'section_identifier',
    'name' => 'Section Display Name',
    'availability' => 85, // percentage available
    'price' => 150, // starting price
    'capacity' => 500, // optional total capacity
]
```

## CSS Integration

All components use the existing customer dashboard CSS framework located at `/public/css/customer-dashboard.css`. The CSS variables and classes are already defined:

- `--primary-green`: Main green color
- `--primary-blue`: Main blue color
- `--gradient-primary`: Primary gradient
- Loading shimmer animations
- Sports-themed color schemes
- Mobile responsive breakpoints

## JavaScript Functionality

Each component includes its own JavaScript for:
- Interactive features (hover effects, tooltips)
- Auto-refresh capabilities
- Data filtering and sorting
- Animation triggers
- Real-time updates simulation

## Loading States

All components include comprehensive loading states with:
- Shimmer placeholder animations
- Skeleton loading layouts
- Proper loading indicators
- Graceful fallbacks when no data is available

## Mobile Responsiveness

Components are fully responsive and include:
- Flexible layouts for mobile devices
- Touch-friendly interactions
- Optimized font sizes and spacing
- Simplified interfaces on smaller screens

## Real-time Features

To implement true real-time functionality, integrate with:
- Laravel WebSockets for live data streaming
- AJAX polling for periodic updates
- Event broadcasting for instant updates
- Database triggers for automatic data refresh

## Performance Considerations

- Components use CSS transforms for smooth animations
- SVG icons for crisp visuals at any scale
- Efficient DOM manipulation with vanilla JavaScript
- Optimized loading states to prevent layout shifts
- Lazy loading for images and heavy content
