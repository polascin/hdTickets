# HD Tickets Customer Dashboard

This document describes the customer dashboard (sports ticket monitoring/purchase) UI/UX and its supporting backend. Note: This platform is for sports event entry tickets, not helpdesk tickets.

## Goals
- Real-time, action-oriented overview for customers
- Clear subscription status and ticket usage
- Fast, mobile-first experience with accessibility and performance focus

## Architecture Overview
- Blade views: canonical customer dashboard served at /dashboard/customer
  - Canonical view: resources/views/dashboard/customer-modern.blade.php
  - Legacy variants (dashboards/customer.blade.php, dashboard/customer-v2*.blade.php) are deprecated
- Controller: ModernCustomerDashboardController (route: GET /dashboard/customer)
- Frontend stack: Blade + Alpine.js + Tailwind CSS + custom CSS (glass theme)
- Real-time: Laravel Echo + broadcasting (with polling fallback)
  - Hydration: data-stats, data-tickets, data-pagination, data-insights, data-flags
  - Feature flags: realtime, infinite_scroll, animations
  - Auth: session + verified + role: customer or admin; scraper denied
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
- AJAX namespace: /ajax/customer-dashboard/* (requires AJAX or expectsJson)
  - GET /stats → { success, data: { available_tickets, new_today, monitored_events, active_alerts, total_savings, price_alerts_triggered }, timestamp }
  - GET /tickets?page=&limit= → { success, data: { tickets: [...], pagination: { current_page, per_page, total, last_page } } }
  - GET /alerts → { success, data: [ { id, user_id, title, criteria, status, created_at, last_checked } ] }
  - GET /recommendations → { success, data: [ ... ] }
  - GET /market-insights → { success, data: { price_trends, platform_performance, demand_analysis, popular_categories, seasonal_trends, recommendation_score, market_summary, user_positioning } }
- View contract (Blade):
  - statistics (alias: stats), active_alerts (alias: alerts)
  - recent_tickets and initial_tickets_page { tickets, pagination }
  - market_insights, quick_actions, subscription_status, feature_flags
- Subscription info: via controller getSubscriptionStatus() with aliases:
  - status, has_active_subscription, is_trial, trial_days_remaining; plan_name, next_billing, usage_stats

All endpoints should:
- Require authentication
- Enforce role-based authorization (customer/admin ok; scraper denied)
- Require AJAX or expectsJson and respond with no-store caching headers
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
- Lazy render large grids; defer non-critical JS; IntersectionObserver sentinel for infinite scroll
- Cache expensive queries; paginate aggressively; short-lived caches for stats
- Cache invalidation via model observers for ScrapedTicket and TicketAlert
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
- php artisan migrate (ensures subscriptions, alerts, scraped_tickets schema is current)
- Horizon running and monitored
- SSL/TLS, security headers, and rate limiting in place
- Warm caches for dashboard aggregates if needed

