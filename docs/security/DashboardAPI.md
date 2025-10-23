# Dashboard API Security and Rate Limiting

This document summarises security and throttling for the customer dashboard APIs.

## Endpoints
- Legacy: `/api/dashboard/{stats,tickets,recommendations}`
- v1: `/api/v1/dashboard/realtime`, `/api/v1/dashboard/analytics-data`, `/api/v1/dashboard/recommendations`, `/api/v1/dashboard/stats` (internal), etc.

## Authentication and Roles
- Session or Sanctum token authentication; verified users only where applicable.
- Role enforcement for v1 routes: customer/admin permitted, scraper denied.

## API Security Middleware
- ApiSecurityMiddleware enforces input sanitisation, security headers, optional API key workflows.
- Authenticated users bypass API key requirement; rate limiting applies via ApiRateLimit.
- Missing User-Agent is allowed for automated tests/integration tools.

## Headers
- JSON endpoints return no-store headers:
  - `Cache-Control: no-store, no-cache, must-revalidate, private`
  - `Pragma: no-cache`
  - `X-Content-Type-Options: nosniff`
- Responses include `X-Request-Id` for correlation.

## Rate Limiting
- Public routes: 10 req/min (configurable via ApiRateLimit)
- Authenticated dashboard API: 120 req/min
- Scraper API: 30–60 req/min

## Client Requirements
- Prefer AJAX requests with `X-Requested-With: XMLHttpRequest` or requests with `Accept: application/json`.
- Use pagination on tickets endpoint; avoid over-fetching.

## Testing
- Feature tests assert: 401 unauthenticated, 403 for scrapers, correct JSON shapes.
- Observer tests ensure caches are invalidated on ticket/alert changes.
