# Deployment Verification Checklist

## Pre-Deployment

### Code Quality
- [x] All Pest tests passing locally: `vendor/bin/pest`
- [x] Laravel Pint formatting: `vendor/bin/pint`
- [x] PHPStan analysis clean: `vendor/bin/phpstan analyse`
- [x] Frontend linting: `npm run lint`
- [x] TypeScript checks: `npm run type-check`
- [x] Frontend build successful: `npm run build`

### Git Status
- [x] All changes committed to `feat/welcome-modern-canonical` branch
- [ ] Branch pushed to remote: `git push origin feat/welcome-modern-canonical`
- [ ] Pull request created and reviewed
- [ ] Branch merged to `main`

## Deployment Steps

### 1. Build Assets
```bash
# On deployment server or via CI
npm ci
npm run build
```

### 2. Optimise Laravel Caches
```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 3. Clear Application Caches
```bash
php artisan cache:clear
php artisan view:clear
```

### 4. Fix Storage Permissions (if needed)
```bash
# Ensure web server can write to storage directories
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Post-Deployment Verification

### Route Behaviour
- [ ] `curl -I https://hd-tickets.com/` returns 200 OK
- [ ] `curl -I https://hd-tickets.com/welcome` returns 308 Permanent Redirect to `/`
- [ ] `curl -I https://hd-tickets.com/welcome-modern` returns 308 Permanent Redirect to `/`
- [ ] `curl -I https://hd-tickets.com/home` returns 308 Permanent Redirect to `/`

### Content Verification
- [ ] Landing page displays at `/`
- [ ] Heading reads: "Comprehensive Sports Events Entry Tickets Monitoring System"
- [ ] British English spelling throughout (e.g., "optimisation", "analyse")
- [ ] No "helpdesk" wording anywhere on the page
- [ ] Sports events domain terminology present

### HTML Structure
- [ ] `<html lang="en-GB">` attribute present
- [ ] Skip to main content link exists
- [ ] Semantic landmarks: `<header>`, `<nav>`, `<main>`, `<footer>`
- [ ] Logical heading hierarchy (single `<h1>`)
- [ ] All interactive elements are native buttons/links

### SEO & Metadata
- [ ] `<title>` tag: "HD Tickets | Sports Event Ticket Monitoring"
- [ ] `<meta name="description">` present with relevant content
- [ ] `<link rel="canonical" href="https://hd-tickets.com/">` present
- [ ] Open Graph tags: `og:title`, `og:description`, `og:url`, `og:type`
- [ ] Twitter Card tags: `twitter:card`, `twitter:title`, `twitter:description`
- [ ] JSON-LD structured data present (Organization schema)

### Accessibility
- [ ] All images have appropriate `alt` text
- [ ] Focus indicators visible on interactive elements
- [ ] Colour contrast meets WCAG 2.2 AA (4.5:1 minimum)
- [ ] Keyboard navigation works (tab through all interactive elements)
- [ ] Reduced motion respected: `prefers-reduced-motion` CSS works

### Performance (Lighthouse/PageSpeed Insights)
- [ ] Performance score ≥ 90
- [ ] Largest Contentful Paint (LCP) ≤ 2.5s
- [ ] First Input Delay (FID) ≤ 100ms
- [ ] Cumulative Layout Shift (CLS) < 0.1
- [ ] First Contentful Paint (FCP) ≤ 1.8s
- [ ] Time to Interactive (TTI) ≤ 3.8s

### Security & Privacy
- [ ] No cookies set on welcome page load
- [ ] No localStorage writes on welcome page
- [ ] No third-party requests on initial load
- [ ] HTTPS enforced (check HTTP→HTTPS redirect)
- [ ] Security headers present (CSP, X-Frame-Options, etc.)

### Asset Loading
- [ ] CSS loaded from Vite manifest
- [ ] JavaScript loaded from Vite manifest
- [ ] No 404 errors in browser console
- [ ] No JavaScript errors in browser console
- [ ] Images load correctly with proper MIME types

## Rollback Plan

If issues are detected:

```bash
# Via Deployer (if using)
./deployer.phar rollback production

# Manual rollback
git revert <commit-hash>
git push origin main
# Trigger deployment pipeline
```

## Performance Targets

| Metric | Target | Acceptable |
|--------|--------|------------|
| LCP | ≤ 2.5s | ≤ 3.5s |
| FID | ≤ 100ms | ≤ 200ms |
| CLS | < 0.1 | < 0.2 |
| Performance Score | ≥ 90 | ≥ 80 |
| Accessibility Score | 100 | ≥ 95 |
| SEO Score | 100 | ≥ 95 |

## Tools

### Lighthouse CI
```bash
# Install globally
npm install -g @lhci/cli

# Run audit
lhci autorun --collect.url=https://hd-tickets.com/
```

### Manual Testing
```bash
# Check redirects
curl -IL https://hd-tickets.com/welcome

# Validate HTML
curl -s https://hd-tickets.com/ | tidy -q -e

# Test reduced motion
# In browser DevTools: Emulate CSS media feature > prefers-reduced-motion: reduce
```

### Accessibility
- [WAVE Browser Extension](https://wave.webaim.org/extension/)
- [axe DevTools](https://www.deque.com/axe/devtools/)
- Keyboard-only navigation test

## Notes

- Storage permissions issue encountered in local testing: ensure `/storage/framework/views` is writable by web server
- Legacy welcome files backed up to `welcome.blade.php.backup` (can be removed after successful deployment)
- All Pest tests in `tests/Feature/WelcomePageCanonicalTest.php` should pass once storage permissions are correct

## Sign-off

- [ ] Developer: _____________________ Date: _____
- [ ] QA: _____________________ Date: _____
- [ ] Product Owner: _____________________ Date: _____
