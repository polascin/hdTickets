# Modern Customer Dashboard Refactor

**Branch**: `refactor/modern-customer-dashboard-cleanup`  
**Date Started**: 2025-10-31  
**Domain**: Sports Events Entry Tickets Monitoring & Purchase System

## Objectives

Remove code mess from the Modern Customer Dashboard while preserving all public contracts, routes, and test behaviour.

## Frozen Contracts (DO NOT CHANGE)

### Routes
- `GET /dashboard/customer` → `dashboard.customer`
- `GET /ajax/customer-dashboard/stats` → `ajax.customer-dashboard.stats`
- `GET /ajax/customer-dashboard/tickets` → `ajax.customer-dashboard.tickets`
- `GET /ajax/customer-dashboard/alerts` → `ajax.customer-dashboard.alerts`
- `GET /ajax/customer-dashboard/recommendations` → `ajax.customer-dashboard.recommendations`
- `GET /ajax/customer-dashboard/market-insights` → `ajax.customer-dashboard.market-insights`

### Controller
- **Name**: `ModernCustomerDashboardController`
- **View**: `dashboard.customer-modern`
- **Public Methods**: `index()`, `getStats()`, `getTickets()`, `getAlerts()`, `getRecommendations()`, `getMarketInsights()`

### API Response Structure
All AJAX endpoints must return:
```json
{
  "success": boolean,
  "data": { ... }
}
```

### View Data Keys
Blade view receives:
- `user`
- `statistics` (alias: `stats`)
- `active_alerts` (alias: `alerts`)
- `recent_tickets`
- `initial_tickets_page`
- `recommendations`
- `market_insights`
- `quick_actions`
- `subscription_status`
- `feature_flags`

### Alpine.js Component
- **Function name**: `modernCustomerDashboard()`
- **Data attributes**: `data-stats`, `data-tickets`, `data-pagination`, `data-insights`, `data-flags`

## Environment Baseline

- **PHP**: 8.3.27
- **Laravel**: 11.46.1
- **Node**: 22.21.0
- **npm**: 10.9.4

## Changes Log

### Phase 1: Baseline & Setup
- ✅ Created refactor branch
- ✅ Verified tooling versions
- ⏳ Capture route snapshot
- ⏳ Run baseline tests

### Phase 2: Quick Wins (Style & Linting)
- ⏳ Run Pint formatting
- ⏳ Run PHPStan analysis
- ⏳ Run npm lint & format

### Phase 3: Controller Cleanup
- ⏳ Extract data prep to Query/Service classes
- ⏳ Add strict types and return types
- ⏳ Remove dead methods
- ⏳ Fix N+1 queries

### Phase 4: View Cleanup
- ⏳ Extract inline styles to dedicated CSS
- ⏳ Fix Tailwind class typos (transition-colours → transition-colors)
- ⏳ Componentise repeated UI
- ⏳ Remove debug/commented code

### Phase 5: JavaScript Cleanup
- ⏳ Remove console emoji logs
- ⏳ Add TypeScript types
- ⏳ Remove unused modules

### Phase 6: Styles & Assets
- ⏳ Remove unused migrated CSS
- ⏳ Remove vite.config.broken.js
- ⏳ Fix z-index, overflow, focus states

### Phase 7: Test & Validate
- ⏳ Run full Pest suite
- ⏳ Manual QA of real-time updates
- ⏳ Production build check

## Notes

- **British English**: Used in all copy, labels, and documentation
- **Framework Keywords**: Tailwind classes, JS APIs remain American spelling
- **Domain**: Sports events entry tickets monitoring (not helpdesk)
- **Testing**: Pest only, no PHPUnit
