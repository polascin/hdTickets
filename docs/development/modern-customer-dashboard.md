# Modern Customer Dashboard

**Domain**: Sports Events Entry Tickets Monitoring & Purchase System  
**Last Updated**: 2025-10-31

## Overview

The Modern Customer Dashboard provides a comprehensive, real-time interface for monitoring sports events entry ticket availability, pricing, and automated purchase opportunities. Built with Laravel 11, Alpine.js, and Tailwind CSS.

## Architecture

### Technology Stack
- **Backend**: Laravel 11, PHP 8.3
- **Frontend**: Alpine.js 3.14, Tailwind CSS, Vite 7
- **Real-time**: Laravel Echo + Pusher WebSockets
- **Testing**: Pest (pestphp/pest ^2.36)

### File Structure

```
app/Http/Controllers/
└── ModernCustomerDashboardController.php    # Main dashboard controller

resources/
├── views/dashboard/
│   └── customer-modern.blade.php            # Dashboard view
├── js/dashboard/
│   └── modern-customer-dashboard.js         # Alpine component
└── css/
    └── dashboard-modern.css                 # Dashboard styles

routes/
└── web.php                                  # Dashboard routes

tests/Feature/
└── ModernCustomerDashboardTest.php          # Feature tests
```

## Public Contracts (DO NOT CHANGE)

### Routes

All routes require `auth` and `verified` middleware. Customer role middleware applied where noted.

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `/dashboard/customer` | `dashboard.customer` | `CustomerMiddleware` |
| GET | `/ajax/customer-dashboard/stats` | `ajax.customer-dashboard.stats` | `CustomerMiddleware` |
| GET | `/ajax/customer-dashboard/tickets` | `ajax.customer-dashboard.tickets` | `CustomerMiddleware` |
| GET | `/ajax/customer-dashboard/alerts` | `ajax.customer-dashboard.alerts` | `CustomerMiddleware` |
| GET | `/ajax/customer-dashboard/recommendations` | `ajax.customer-dashboard.recommendations` | `CustomerMiddleware` |
| GET | `/ajax/customer-dashboard/market-insights` | `ajax.customer-dashboard.market-insights` | `CustomerMiddleware` |

### Controller Methods

**ModernCustomerDashboardController**

```php
public function index(): View
public function getStats(Request $request): JsonResponse
public function getTickets(Request $request): JsonResponse
public function getAlerts(Request $request): JsonResponse
public function getRecommendations(Request $request): JsonResponse
public function getMarketInsights(Request $request): JsonResponse
```

### API Response Format

All AJAX endpoints return:
```json
{
  "success": true,
  "data": { ... },
  "timestamp": "2025-10-31T14:28:50.000000Z"
}
```

### View Data Contract

The Blade view `dashboard.customer-modern` expects:

```php
[
    'user' => User,                           // Authenticated user with relationships
    'statistics' => array,                     // Dashboard statistics
    'stats' => array,                          // Alias for statistics (backward compat)
    'active_alerts' => Collection,             // User's active ticket alerts
    'alerts' => Collection,                    // Alias for active_alerts (backward compat)
    'recent_tickets' => Collection,            // Recent ticket listings
    'initial_tickets_page' => array,           // Paginated tickets with metadata
    'recommendations' => array,                // Personalized recommendations
    'market_insights' => array,                // Market analytics data
    'quick_actions' => array,                  // Quick action links
    'subscription_status' => array,            // User subscription information
    'feature_flags' => array,                  // Feature toggle flags
]
```

### Alpine.js Component

**Function Name**: `modernCustomerDashboard()`

**Data Attributes** on root element:
- `data-stats` - Initial statistics JSON
- `data-tickets` - Initial tickets array JSON
- `data-pagination` - Initial pagination metadata
- `data-insights` - Initial market insights JSON
- `data-flags` - Feature flags JSON

## Features

### Real-time Updates
- Live statistics refresh every 30 seconds
- WebSocket integration for price changes and availability
- Automatic reconnection handling
- Background tab detection with pause/resume

### Interactive Components
- **Statistics Cards**: Available tickets, active alerts, monitored events, total savings
- **Ticket Listings**: Infinite scroll pagination, real-time price updates
- **Alert Management**: Active alerts with status indicators
- **Market Insights**: Trending categories, price alerts, market activity

### Responsive Design
- Mobile-first approach with collapsible sidebar
- Glass morphism design with backdrop blur effects
- Touch-friendly interactions
- Dark mode support via `prefers-color-scheme`
- Reduced motion support via `prefers-reduced-motion`

### Accessibility
- Semantic HTML with ARIA attributes
- Keyboard navigation support (shortcuts: 1-4 for tabs, Ctrl+R for refresh)
- Focus management and visible focus indicators
- Screen reader friendly

## Data Flow

### Initial Page Load
1. User navigates to `/dashboard/customer`
2. `ModernCustomerDashboardController@index` executes
3. Data cached for 5 minutes (`customer_dashboard_{user_id}`)
4. View rendered with server-side data
5. Alpine.js component initializes client-side
6. WebSocket connection established (if available)

### AJAX Updates
1. Alpine component calls API endpoints every 30 seconds
2. Controller methods check authorization
3. Cached data returned (15-60 second TTL depending on endpoint)
4. Alpine updates reactive state
5. View re-renders changed sections automatically

### Real-time Updates
1. Backend emits price or availability change event
2. Laravel Echo receives event via Pusher
3. Alpine handles event and updates state
4. Visual indicators (animations, badges) reflect changes
5. Notifications shown for significant changes

## Caching Strategy

| Data Type | TTL | Cache Key Pattern |
|-----------|-----|-------------------|
| Dashboard Statistics | 5 minutes | `customer_dashboard_{user_id}` |
| Real-time Stats | 15 seconds | `stats:available_tickets`, `stats:new_today` |
| User Stats | 15 seconds | `stats:user:{user_id}:active_alerts` |
| Unique Events | 60 seconds | `stats:unique_events` |
| Average Price | 60 seconds | `stats:average_price` |

## Extending the Dashboard

### Adding New Widgets

1. **Backend**: Add method to controller
```php
public function getNewWidget(Request $request): JsonResponse
{
    $user = Auth::user();
    $data = Cache::remember("widget_{$user->id}", 300, fn() => [
        // Widget data computation
    ]);
    
    return response()->json([
        'success' => true,
        'data' => $data,
    ]);
}
```

2. **Route**: Add to `routes/web.php`
```php
Route::get('/new-widget', [ModernCustomerDashboardController::class, 'getNewWidget'])
    ->name('customer-dashboard.new-widget');
```

3. **Alpine**: Update component
```javascript
// In modern-customer-dashboard.js
newWidgetData: [],

async loadNewWidget() {
    const response = await this.apiCall('/ajax/customer-dashboard/new-widget');
    if (response.success) {
        this.newWidgetData = response.data;
    }
}
```

4. **View**: Add UI in Blade template
```blade
<div x-show="activeTab === 'dashboard'">
    <div class="glass-card rounded-xl p-6">
        <h3 x-text="newWidgetData.title"></h3>
        <!-- Widget content -->
    </div>
</div>
```

### Adding Real-time Features

1. **Broadcasting Event**
```php
broadcast(new TicketPriceChanged($ticket));
```

2. **Alpine Handler**
```javascript
window.realTimeDashboard.on('ticketPriceChanged', (event) => {
    this.handlePriceChange(event);
});
```

## Testing

### Running Tests

```bash
# All dashboard tests
vendor/bin/pest tests/Feature/ModernCustomerDashboardTest.php

# Specific test
vendor/bin/pest --filter="customer_can_access_modern_dashboard"

# With coverage
vendor/bin/pest tests/Feature/ModernCustomerDashboardTest.php --coverage
```

### Test Coverage

Existing tests cover:
- Route authorization and access control
- View data contract validation
- AJAX endpoint response structure
- Pagination behaviour
- Error handling and fallbacks
- Subscription status determination

### Writing New Tests

```php
test('new feature works correctly', function () {
    $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
    
    $response = $this->actingAs($customer)
        ->getJson('/ajax/customer-dashboard/new-feature');
    
    $response->assertStatus(200)
        ->assertJson(['success' => true])
        ->assertJsonStructure(['data' => ['expected_key']]);
});
```

## Performance Considerations

### Backend Optimizations
- Query result caching (5-60 second TTL)
- Eager loading of relationships (`user.load(['subscription', 'preferences'])`)
- Database query optimization (indexes on frequently filtered columns)
- Pagination for large result sets (20 items per page default)

### Frontend Optimizations
- Vite code splitting for dashboard-specific code
- Debounced API calls (100ms for scroll, 1s for search)
- Intersection Observer for infinite scroll (vs scroll event)
- Conditional real-time connection (only when tab visible)
- Tree-shaking of unused Tailwind utilities

### Monitoring
- Frontend: `localStorage.setItem('last_dashboard_error', JSON.stringify(context))`
- Backend: Laravel log channels for controller errors
- Real-time: Echo connection status tracking

## Troubleshooting

### Common Issues

**Dashboard doesn't load (500 error)**
- Check storage/logs/laravel.log for PHP errors
- Verify database connection
- Clear cache: `php artisan cache:clear`

**Real-time updates not working**
- Verify Pusher credentials in `.env`
- Check browser console for WebSocket errors
- Ensure Laravel Echo is properly initialized

**Styles not applying**
- Run `npm run build` to compile assets
- Clear browser cache
- Verify `@vite()` directives in Blade

**Tests failing**
- Run migrations: `php artisan migrate --env=testing`
- Seed test data: `php artisan db:seed --env=testing`
- Check PHPStan level 8 compliance

## Security

- CSRF protection on all AJAX requests
- Authentication required for all endpoints
- Rate limiting: 60 requests/minute per user
- Input validation on all user-provided data
- XSS protection via Blade escaping
- Role-based access control (Customer + Admin only)

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

Graceful degradation for:
- JavaScript disabled: Server-rendered content remains visible
- WebSocket unavailable: Falls back to AJAX polling
- Older browsers: Progressive enhancement approach

## Related Documentation

- [WARP.md](/var/www/hdtickets/WARP.md) - Development environment and tooling
- [Refactor Log](/var/www/hdtickets/docs/development/modern-customer-dashboard-refactor.md) - Recent cleanup changes
- [README.md](/var/www/hdtickets/README.md) - Project overview
