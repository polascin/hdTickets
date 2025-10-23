# Customer Dashboard QA Checklist

This checklist validates the modern customer dashboard at `/dashboard/customer`.

## Access and Roles
- [ ] Customer (verified) can access `/dashboard/customer` (200)
- [ ] Admin (verified) can access (200)
- [ ] Agent gets 403
- [ ] Scraper/guest denied (403/302 login)

## Hydration and Rendering
- [ ] Root element contains: data-stats, data-tickets, data-pagination, data-insights, data-flags
- [ ] Stats tiles render values; price trend badge renders when available
- [ ] Initial tickets list shows up to 20 items
- [ ] Quick actions link to tickets main, alerts create/index, profile

## AJAX Endpoints (require AJAX or expectsJson)
- [ ] GET /ajax/customer-dashboard/stats → 200, JSON: { success, data, timestamp }
- [ ] GET /ajax/customer-dashboard/tickets?page=&limit= → 200, JSON with pagination
- [ ] GET /ajax/customer-dashboard/alerts → 200, JSON array
- [ ] GET /ajax/customer-dashboard/recommendations → 200
- [ ] GET /ajax/customer-dashboard/market-insights → 200; contains contract keys
- [ ] Non-AJAX requests receive 403
- [ ] Responses include Cache-Control: no-store and X-Content-Type-Options: nosniff

## Infinite Scroll and Pagination
- [ ] IntersectionObserver sentinel triggers loading page 2 when near bottom
- [ ] Fallback scroll listener works if IO unavailable
- [ ] Tickets append without duplication; `hasMoreTickets` toggles at end

## Real-time / Auto-refresh
- [ ] Auto-refresh stats every ~30s when tab visible
- [ ] Stops when page hidden; resumes on visibility
- [ ] Real-time events (if configured) update stats without errors

## Alerts
- [ ] Recent alerts section renders last 3; statuses indicated by colour and label
- [ ] Alerts endpoint includes `user_id` for each record

## Subscription Status
- [ ] Active plan shows plan_name; trial shows trial days remaining
- [ ] Usage bar reflects alerts used/limit

## British English & Accessibility
- [ ] Spelling uses British English (colour, organise, etc.)
- [ ] Keyboard navigation: tabs, load more button
- [ ] Sufficient colour contrast; aria attributes present where applicable

## Performance & Caching
- [ ] Stats endpoints quick (<200ms on warm cache)
- [ ] Cache invalidates on ticket/alert changes

## Console/Network Cleanliness
- [ ] No uncaught errors in console
- [ ] No 404 assets; CORS errors absent

## Mobile
- [ ] Sidebar toggle works; closes on outside click
- [ ] Layout adapts for <768px width
