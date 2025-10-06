# React Features Backend Integration

This document outlines the integration between the React-based advanced features and the Laravel backend API for the HD Tickets system.

## Overview

The integration provides a comprehensive set of React components with Laravel backend APIs for advanced ticket monitoring and management features.

## Features Implemented

### 1. Smart Ticket Monitoring Dashboard
- **Component**: `SmartTicketMonitoringDashboard`
- **API Controller**: `TicketMonitoringController`
- **Endpoints**: 
  - `GET /api/v1/monitoring/dashboard` - Dashboard data
  - `POST /api/v1/monitoring/tickets/{id}/toggle` - Toggle monitoring
  - `POST /api/v1/monitoring/settings` - Update settings
  - `GET /api/v1/monitoring/tickets/{id}` - Ticket details

### 2. Advanced Search & Filtering System
- **Component**: `AdvancedSearchSystem`
- **API Controller**: `AdvancedSearchController`
- **Endpoints**:
  - `GET /api/v1/search/advanced` - Advanced search with filters
  - `GET /api/v1/search/suggestions` - Search suggestions
  - `GET /api/v1/search/popular` - Popular searches

### 3. Team & Venue Following System
- **Component**: `FollowingSystem`
- **API Controller**: `FollowingController`
- **Endpoints**:
  - `GET /api/v1/following/dashboard` - Following dashboard
  - `GET /api/v1/following/` - Get followed items
  - `GET /api/v1/following/discover` - Discover recommendations
  - `POST /api/v1/following/follow` - Follow team/venue
  - `POST /api/v1/following/unfollow` - Unfollow team/venue
  - `POST /api/v1/following/notifications/toggle` - Toggle notifications

### 4. Ticket Comparison Engine
- **Component**: `TicketComparisonEngine`
- **API Controller**: `TicketComparisonController`
- **Endpoints**:
  - `GET /api/v1/comparison/compare` - Compare tickets
  - `POST /api/v1/comparison/detailed` - Detailed comparison
  - `GET /api/v1/comparison/platforms` - Platform comparison
  - `GET /api/v1/comparison/value-analysis` - Value analysis

### 5. Interactive Event Calendar
- **Component**: `InteractiveEventCalendar`
- **API Controller**: Uses existing `EventController` and analytics APIs
- **Note**: Calendar component uses standard event and ticket APIs

### 6. User Notification System
- **Component**: `UserNotificationSystem`
- **API Controller**: Uses existing notification APIs
- **Note**: Notification system integrates with existing Laravel notification infrastructure

### 7. Ticket History Tracking
- **Component**: `TicketHistoryTracking`
- **API Controller**: Uses existing ticket and price history APIs
- **Note**: History tracking uses existing ticket and analytics APIs

### 8. Social Proof Features
- **Component**: `SocialProofFeatures`
- **API Controller**: `SocialProofController`
- **Endpoints**:
  - `GET /api/v1/social/dashboard` - Social proof dashboard
  - `GET /api/v1/social/events/proof` - Event social proof
  - `GET /api/v1/social/trending` - Trending events
  - `GET /api/v1/social/demand-indicators` - Demand indicators
  - `GET /api/v1/social/activity-feed` - Real-time activity feed
  - `GET /api/v1/social/user-behaviour` - User behaviour insights
  - `GET /api/v1/social/tickets/{id}/proof` - Ticket social proof

## Models Created

### Core Models
- `Event` - Sports events model
- `Team` - Sports teams model  
- `Venue` - Sports venues model
- `Following` - User following relationships (polymorphic)

### Relationships
- Teams and Venues have polymorphic following relationships
- Events belong to venues
- Following model supports both teams and venues
- Users can follow teams and venues with notification preferences

## API Routes Structure

All routes are grouped under `/api/v1/` and require authentication:

```php
Route::prefix('v1')->middleware(['auth:sanctum', 'api.rate_limit'])->group(function () {
    // Smart Monitoring Routes
    Route::prefix('monitoring')->name('api.monitoring.')->group(...);
    
    // Advanced Search Routes  
    Route::prefix('search')->name('api.search.')->group(...);
    
    // Following System Routes
    Route::prefix('following')->name('api.following.')->group(...);
    
    // Comparison Engine Routes
    Route::prefix('comparison')->name('api.comparison.')->group(...);
    
    // Social Proof Routes
    Route::prefix('social')->name('api.social.')->group(...);
});
```

## Web Interface

### Dashboard Route
Access the React features dashboard at: `/dashboard/react-features`

### Blade Template
The main dashboard view is located at: `resources/views/dashboard/react-features.blade.php`

### Features
- Tab-based navigation between different React components
- Loading states and error handling
- Responsive design with Tailwind CSS
- Alpine.js for tab management
- Automatic React component initialization

## Authentication & Authorization

### API Authentication
- Uses Laravel Sanctum for API token authentication
- CSRF protection for web routes
- Rate limiting applied to all API routes

### Permissions
- All features available to authenticated users
- Role-based access control for sensitive operations
- Admin users have full access to all features

## Caching Strategy

### API Caching
- Dashboard data cached for 5 minutes (300 seconds)
- Search results cached for 10 minutes (600 seconds)  
- Social proof data cached for 15 minutes (900 seconds)
- User-specific caches include user ID in cache keys

### Cache Management
- Automatic cache invalidation on data changes
- Manual cache clearing for admin users
- Cache tags for efficient selective clearing

## Database Schema Requirements

### Required Tables
- `events` - Sports events
- `teams` - Sports teams  
- `venues` - Sports venues
- `followings` - User following relationships (polymorphic)
- Existing `tickets`, `ticket_alerts`, `users` tables

### Key Fields
- All models use UUID primary keys
- Polymorphic relationships in followings table
- Popularity scores for trending calculations
- Notification preferences in followings

## Error Handling

### API Error Responses
- Consistent JSON error responses
- HTTP status codes (400, 401, 404, 422, 500)
- User-friendly error messages
- Detailed validation errors

### Frontend Error Handling
- Loading states during API calls
- Error message display
- Retry mechanisms for failed requests
- Graceful degradation

## Performance Considerations

### API Optimization
- Efficient database queries with proper relationships
- Pagination for large datasets
- Database indexing on frequently queried fields
- Eager loading to prevent N+1 queries

### Frontend Optimization
- Lazy loading of React components
- Efficient re-rendering with proper state management
- Debounced search requests
- Optimistic updates for better UX

## Future Enhancements

### Planned Features
- Real-time WebSocket updates
- Push notifications
- Advanced analytics integration
- Mobile app API compatibility
- Enhanced social features

### Scalability
- Redis caching for high-traffic scenarios
- Database read replicas for analytics
- CDN integration for static assets
- API versioning for backward compatibility

## Development Setup

### Frontend Development
1. React components are initialized via global window objects
2. Components expect API base URL and CSRF token
3. User authentication passed as props
4. Components handle their own state management

### Backend Development
1. Controllers follow standard Laravel patterns
2. Models use UUID traits and relationships
3. API responses use consistent JSON structure
4. Validation rules defined in controller methods

## Testing

### API Testing
- Unit tests for controller methods
- Feature tests for complete API workflows
- Authentication and authorization testing
- Performance testing for heavy operations

### Frontend Testing
- Component unit tests
- Integration tests with mock APIs
- E2E tests for complete user workflows
- Accessibility testing

---

This integration provides a solid foundation for advanced ticket monitoring features while maintaining Laravel best practices and ensuring scalability.