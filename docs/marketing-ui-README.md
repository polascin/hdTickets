# HD Tickets Marketing UI

## Overview
Complete marketing website implementation for HD Tickets, featuring modern design patterns inspired by TicketScoutie while maintaining HD Tickets branding and identity.

## Quick Start

### Accessing the Pages
- **Landing Page**: `/new` (preview) - Will eventually be at `/`
- **Pricing**: `/pricing`
- **Coverage**: `/coverage`
- **FAQs**: `/faqs`

### Local Development
```bash
# Start dev server with hot reload
npm run dev

# Build for production
npm run build

# Run tests
vendor/bin/pest tests/Feature/PublicPagesTest.php
```

## Architecture

### Pages
All pages extend `layouts/marketing.blade.php` and follow consistent structure:

1. **Home** (`resources/views/public/home.blade.php`)
   - Hero with search
   - Quick stats (cached)
   - Features grid
   - How it works
   - CTA sections

2. **Pricing** (`resources/views/public/pricing.blade.php`)
   - Three-tier pricing
   - Feature comparison
   - Integrated FAQs

3. **Coverage** (`resources/views/public/coverage.blade.php`)
   - Sport categories
   - Platform listings
   - Geographic coverage

4. **FAQs** (`resources/views/public/faqs.blade.php`)
   - Alpine.js accordion
   - Four categories
   - Accessible markup

### Layout System
```
layouts/marketing.blade.php
├── public/partials/header.blade.php (Navigation)
├── @yield('content')                 (Page content)
└── public/partials/footer.blade.php  (Footer)
```

### Assets Pipeline
```
resources/
├── css/marketing.css    → public/build/assets/marketing-*.css
└── js/marketing.js      → public/build/assets/marketing-*.js
```

## Features

### SEO Optimization
✅ **Meta Tags**
- Title, description, keywords
- Open Graph (Facebook)
- Twitter Card
- Canonical URLs

✅ **Structured Data**
```json
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "HD Tickets",
  "applicationCategory": "SportsApplication"
}
```

✅ **Performance**
- Font preconnect/preload
- 10-minute stats caching
- Optimized images (WebP)
- Minimal JS bundle

### Accessibility (WCAG 2.1 AA)
✅ **Navigation**
- Skip to main content
- Keyboard navigation
- Focus indicators
- ARIA attributes

✅ **Content**
- Semantic HTML
- Landmark regions
- Proper heading hierarchy
- Alt text on images

✅ **Interactive**
- Accordion with ARIA
- Mobile menu trap focus
- Form labels
- Button states

### British English
All content uses British English:
- Spelling: favourite, customise, optimise, colour
- Currency: £ (GBP)
- Date formats: DD/MM/YYYY

## Configuration

### Environment Variables
Add to `.env`:
```env
# Google Analytics 4 (optional)
GA4_ID=G-XXXXXXXXXX
```

### Analytics
GA4 tracking is:
- ✅ Conditional (only if GA4_ID is set)
- ✅ Privacy-conscious (respects Do Not Track)
- ✅ GDPR compliant

### Caching
Stats are cached for performance:
```php
Cache::remember('public.landing.stats', 600, function () {
    // Fetch stats...
});
```

**Cache Duration**: 10 minutes (600 seconds)

**Clear Cache**:
```bash
php artisan cache:clear
```

## Testing

### Running Tests
```bash
# All public page tests
vendor/bin/pest tests/Feature/PublicPagesTest.php

# With coverage
vendor/bin/pest --coverage tests/Feature/PublicPagesTest.php

# Filter specific test
vendor/bin/pest --filter="home page returns successful response"
```

### Test Coverage
- ✅ 25 tests covering all pages
- ✅ SEO and meta tags
- ✅ Accessibility features
- ✅ Authentication flows
- ✅ British English compliance
- ✅ Caching behavior

## Deployment

### Pre-Deployment Checklist
- [ ] Run `npm run build` successfully
- [ ] Run tests: `vendor/bin/pest tests/Feature/PublicPagesTest.php`
- [ ] Verify routes: `php artisan route:list --name=public`
- [ ] Check British English spelling
- [ ] Test responsive design (mobile/tablet/desktop)
- [ ] Verify all links work
- [ ] Test hero search functionality
- [ ] Verify stats display correctly

### Build Assets
```bash
# Production build
npm run build

# Verify build output
ls -lh public/build/assets/marketing-*
```

### Deploy Steps
1. Merge feature branch to main
2. Run migrations (if any): `php artisan migrate`
3. Build assets: `npm run build`
4. Clear caches: `php artisan optimize:clear`
5. Optimize: `php artisan optimize`

### Go-Live (Route Flip)
To make landing page the default home:

**Option 1: Immediate**
```php
// routes/web.php
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return app(PublicController::class)->home(request());
})->name('home');
```

**Option 2: Gradual**
1. Keep `/new` as landing for testing
2. Monitor analytics and feedback
3. Flip route when confident
4. Keep `/new` as alias temporarily

## Rollback Plan

### If Issues Arise
1. **Route Rollback**
   ```bash
   # Revert routes/web.php to previous version
   git checkout HEAD~1 routes/web.php
   ```

2. **Clear Caches**
   ```bash
   php artisan optimize:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

3. **Rebuild Assets** (if needed)
   ```bash
   npm run build
   ```

### Pages Remain Accessible
Even after rollback:
- `/new` - Landing page
- `/pricing` - Pricing page
- `/coverage` - Coverage page
- `/faqs` - FAQs page

## Maintenance

### Updating Content
Content is in Blade templates:
```
resources/views/public/
├── home.blade.php      (Landing page content)
├── pricing.blade.php   (Plans and pricing)
├── coverage.blade.php  (Sports and platforms)
└── faqs.blade.php      (Questions and answers)
```

### Adding New FAQ
```php
// resources/views/public/faqs.blade.php
<div class="border border-gray-200 rounded-lg bg-white">
  <button 
    @click="openFaq = openFaq === 'new1' ? null : 'new1'"
    class="w-full px-6 py-4 text-left..."
    :aria-expanded="(openFaq === 'new1').toString()"
    aria-controls="faq-new1">
    <span class="font-semibold text-gray-900">Your Question?</span>
    <svg class="w-5 h-5 text-gray-500 transition-transform..."...>
  </button>
  <div x-show="openFaq === 'new1'" x-collapse id="faq-new1" class="px-6 pb-4">
    <p class="text-gray-600">Your answer here.</p>
  </div>
</div>
```

### Updating Pricing
Edit `resources/views/public/pricing.blade.php`:
```html
<span class="text-5xl font-bold text-gray-900">£XX.XX</span>
```

### Updating Stats
Stats are automatically fetched from database. To customize:
```php
// app/Http/Controllers/PublicController.php
private function getCachedStats(): array
{
    return Cache::remember('public.landing.stats', 600, function (): array {
        return [
            'total_tickets' => YourLogic::here(),
            'platforms'     => 40,
            'cities'        => 50,
        ];
    });
}
```

## Monitoring

### Analytics Events
Track with GA4 (if configured):
- `page_view` - Automatic
- `search` - Hero search
- `click` - CTA buttons
- `conversion` - Registration

### Performance Metrics
Monitor via Lighthouse or similar:
- **LCP** (Largest Contentful Paint): < 2.5s
- **FID** (First Input Delay): < 100ms
- **CLS** (Cumulative Layout Shift): < 0.1
- **Accessibility**: 100/100

### Error Monitoring
Check logs for:
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/apache2/error.log
```

## Troubleshooting

### Assets Not Loading
```bash
# Rebuild assets
npm run build

# Clear view cache
php artisan view:clear

# Check permissions
chmod -R 755 public/build
```

### Routes Not Found
```bash
# Clear route cache
php artisan route:clear

# List routes
php artisan route:list --name=public
```

### Stats Not Showing
```bash
# Clear cache
php artisan cache:clear

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### Styling Issues
```bash
# Rebuild CSS
npm run build

# Check Tailwind config
cat tailwind.config.js

# Verify content paths include public views
```

## Browser Support
- Chrome/Edge: Last 2 versions
- Firefox: Last 2 versions
- Safari: Last 2 versions
- Mobile browsers: iOS Safari 12+, Chrome Android

## Contributing

### Code Style
- Follow PSR-12 for PHP
- Use Laravel Pint: `vendor/bin/pint`
- British English in all user-facing text
- Semantic HTML
- Accessible markup (ARIA)

### Testing
All new features must include tests:
```php
test('new feature works', function (): void {
    $response = get(route('your.route'));
    $response->assertStatus(200);
});
```

## Support

### Documentation
- [Main Project Docs](../README.md)
- [Architecture Docs](./architecture/)
- [Development Guides](./development/)

### Getting Help
- Check [FAQ page](/faqs)
- Review tests for examples
- Check commit history for changes

## License
Proprietary - HD Tickets © 2025
