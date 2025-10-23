# Deployment and Rollback Plan: Modern Customer Dashboard

## Pre-deployment
- [ ] Merge feature branch after CI is green
- [ ] Announce maintenance window if necessary
- [ ] Backup DB (schema + data as per policy)
- [ ] Verify env settings (CACHE, QUEUE, HORIZON, ECHO, REDIS)

## Deployment sequence
1. composer install --no-dev --prefer-dist --optimize-autoloader
2. npm ci && npm run build
3. php artisan migrate --force
4. php artisan config:cache && php artisan route:cache && php artisan view:cache
5. Restart queue workers / Horizon (if needed)
6. Warm critical caches (optional):
   - Stats: trigger /ajax/customer-dashboard/stats as a smoke test

## Post-deployment validation
- [ ] Open /dashboard/customer as customer + admin (200)
- [ ] AJAX endpoints (stats, tickets, alerts, recommendations, insights) return 200
- [ ] Infinite scroll loads page 2
- [ ] No console errors; network clean

## Rollback
1. php artisan migrate:rollback --step=1 (or to previous tag)
2. Revert to previous release/tag
3. php artisan config:clear && php artisan route:clear && php artisan view:clear
4. Verify /dashboard/customer loads with 200

## Observability
- X-Request-Id is returned per request for correlation
- Monitor 4xx/5xx rates on /dashboard/customer and /ajax/customer-dashboard/*
- Log anomalies in recommendation/insights generation (already handled by controller try/catch)
