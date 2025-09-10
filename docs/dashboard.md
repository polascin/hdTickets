# HD Tickets Customer Dashboard

This document describes the customer dashboard (sports ticket monitoring/purchase) UI/UX and its supporting backend. Note: This platform is for sports event entry tickets, not helpdesk tickets.

## Goals
- Real-time, action-oriented overview for customers
- Clear subscription status and ticket usage
- Fast, mobile-first experience with accessibility and performance focus

## Architecture Overview
- Blade views: enhanced customer dashboard template served at /dashboard/customer
- Controller: EnhancedDashboardController rendering the enhanced dashboard view
- Frontend stack: Blade + Alpine.js + Tailwind CSS + custom CSS (glass theme)
- Real-time: Laravel Echo + broadcasting (with polling fallback)
- Data: Aggregated metrics from services (AnalyticsService, RecommendationService, etc.)

## Key UI Modules
- Header: greeting, quick actions
- Stats tiles: aggregated metrics (available tickets, monitored events, alerts, etc.)
- Tickets grid: recent tickets with indicators (demand, price trend)
- Filters/search: sport/platform/price/date/sort with presets
- Recommendations: personalized picks (RecommendationService)
- Alerts: create/manage alerts per user
- Subscription status widget: plan, free trial days remaining, usage bar, manage link
- Notifications: toast/messages for updates

## Data Sources and API Contracts
- Dashboard tiles endpoint: returns key-value metrics for each tile
- Tickets endpoint: paginated list filtered by query parameters
- Recommendations endpoint: list of recommended tickets for the user
- Alerts endpoint: CRUD for user alerts
- Subscription info: from User model helpers (ticket limits, usage, free trial days)

All endpoints should:
- Require authentication
- Enforce role-based authorization (customer/admin ok; scraper denied)
- Respect rate limiting and return consistent JSON shapes

## Real-time Updates
- WebSocket channels with Echo when configured
- Fallback to polling (e.g., 30–60s) for metrics and tickets grid
- Client cache to reduce duplicate fetches; optimistic UI for alert changes

## Configuration
- SUBSCRIPTION_* and PURCHASE_* env vars
- Echo/Pusher keys for real-time
- Horizon for queue workers

## Accessibility
- Semantic markup (landmark roles, headings)
- Focus management for modals/menus
- Color contrast compliant; usage thresholds use color + labels
- Keyboard navigable filter controls

## Performance
- Lazy render large grids; defer non-critical JS
- Cache expensive queries; paginate aggressively
- Minimize reflow: use transforms for animations; avoid heavy blur on mobile

## Troubleshooting
- 403 on dashboard: verify middleware and role (customer/admin allowed; scraper denied)
- Empty stats: check services wired for the controller and permissions
- Z-index/backdrop issues: verify stacking contexts of dropdowns/modals and blur support
- Route issues: ensure subscription routes use `subscription.plans`

## Testing Strategy (see tests/*)
- Unit: User subscription helpers, services business logic
- Feature: Access controls, endpoints payload shape, filtering
- Integration: Monthly usage aggregation, cross-component flows

## Deployment Checklist
- npm run build and asset versioning
- artisan config:cache/route:cache/view:cache
- Horizon running and monitored
- SSL/TLS, security headers, and rate limiting in place

