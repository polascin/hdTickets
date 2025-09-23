# Dashboard Architecture

This document summarizes the structure and data flow for the enhanced Customer Dashboard.

## Overview
The dashboard provides:
- Real-time statistics & recent tickets (realtime endpoint)
- Analytics & trends (analytics endpoint)
- Modular Alpine.js component for UI state
- Service & Resource layers for clean separation

## Layers
1. Controller: `EnhancedDashboardController`
   - Endpoints: `getRealtimeData`, `getAnalytics`, page `index`
   - Attaches ETag & Last-Modified headers (conditional 304 support)
2. Services:
   - `TicketStatsService`: aggregates ticket stats
   - `AnalyticsService`: constructs analytics payload
   - `UserMetricsService`, `RecommendationService`, `AlertService` (domain-specific data)
3. Resources:
   - `DashboardRealtimeResource`
   - `DashboardAnalyticsResource`
   - `TicketSummaryResource` (normalizes recent tickets)
4. Frontend:
   - Dedicated Vite entry: `resources/js/dashboard/index.js`
   - Alpine component factory: `customer-v3.js` (auto-register + initial state hydration via `window.__DASHBOARD_INITIAL__`)

## Response Contracts
Realtime (`/api/v1/dashboard/realtime`):
```
{
  success: true,
  data: {
    statistics: {},
    recent_tickets: [ TicketSummaryResource ],
    user_metrics: {},
    system_status: {},
    notifications: {},
    last_updated: ISO8601
  },
  meta: { refresh_interval, cache_status, user_id }
}
```

Analytics (`/api/v1/dashboard/analytics-data`):
```
{
  success: true,
  data: {
    generated_at: ISO8601,
    totals: { available_tickets, unique_events },
    trends: { demand: { high_demand, demand_percentage }, pricing: [] },
    platforms: []
  },
  meta: { user_id, generated_at }
}
```

## Caching & Freshness
- Realtime data cached 2 minutes (key: `dashboard_realtime_data:{user}`)
- Complete page data cached 5 minutes
- Recent tickets cached per-user for 3 minutes
- Trending events cached 10 minutes

## Conditional Requests
Both realtime and analytics endpoints:
- Compute weak ETag as SHA-1 hash of JSON payload subset
- Set `Last-Modified` based on `last_updated` / `generated_at`
- Return 304 on matching `If-None-Match` or `If-Modified-Since`

## Frontend Data Hydration
Blade view seeds:
```
window.__DASHBOARD_INITIAL__ = {
  statistics, recent_tickets, system_status, notifications
}
```
Alpine component consumes and then performs background refreshes.

## Testing
- Feature tests for realtime & analytics endpoints.
- Vitest spec validates selected Alpine module behaviors (formatNumber, retry logic).

## Extension Points
- Introduce granular resources for metrics & notifications.
- Add WebSocket push for lower-latency updates.
- Implement client-side diffing for partial UI updates.

## Build Split
- Core app bundle: `resources/js/app.js`
- Dashboard-specific bundle: `resources/js/dashboard/index.js` (reduces base payload for non-dashboard pages)

---
This document should be updated when additional resources, caching strategies, or delivery mechanisms (e.g., SSE or WebSockets) are introduced.
