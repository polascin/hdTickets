# HD Tickets Performance Optimization Guide

This guide documents the comprehensive performance optimizations implemented for HD Tickets to improve Core Web Vitals, reduce layout shifts, and enhance user experience.

## 🚀 Performance Systems Overview

### 1. Performance Monitor (`performance-monitor.js`)
Tracks and reports Core Web Vitals in real-time:
- **LCP (Largest Contentful Paint)**: Target < 2.5s
- **FID (First Input Delay)**: Target < 100ms  
- **CLS (Cumulative Layout Shift)**: Target < 0.1
- **FCP (First Contentful Paint)**: Target < 1.8s
- **TTI (Time to Interactive)**: Target < 3.8s

### 2. Lazy Loading System (`lazy-loading.js`)
Efficient loading of images and components:
- Images with placeholders and fade-in effects
- Heavy JavaScript components on-demand
- Intersection Observer for viewport detection
- Skeleton loaders for better perceived performance

### 3. Critical CSS Optimizer (`critical-css-optimizer.js`)
Optimizes CSS delivery:
- Extracts above-the-fold critical CSS
- Defers non-critical stylesheets
- Prevents FOUC (Flash of Unstyled Content)
- Optimizes font loading with `font-display: swap`

## 📊 Core Web Vitals Targets

| Metric | Good | Needs Improvement | Poor |
|--------|------|------------------|------|
| **LCP** | ≤ 2.5s | 2.5s - 4.0s | > 4.0s |
| **FID** | ≤ 100ms | 100ms - 300ms | > 300ms |
| **CLS** | ≤ 0.1 | 0.1 - 0.25 | > 0.25 |

## 🔧 Implementation Details

### Performance Monitoring

#### Automatic Metrics Collection
```javascript
// Access current performance metrics
const metrics = window.getPerformanceMetrics();
console.log('Core Web Vitals:', metrics.coreWebVitals);
```

#### Real-time Alerts
The system automatically warns about:
- Layout shifts > 0.05
- Slow resources > 1s load time
- Long tasks > 100ms
- High memory usage > 90%

#### Server Reporting
Enable server reporting for analytics:
```javascript
window.HDTickets.PerformanceMonitor.enableReporting('/api/performance-metrics');
```

### Lazy Loading Implementation

#### Image Lazy Loading
Images are automatically lazy loaded using:
```html
<!-- Basic lazy loading -->
<img data-src="/path/to/image.jpg" alt="Description" loading="lazy">

<!-- With dimensions (prevents layout shift) -->
<img data-src="/path/to/image.jpg" width="800" height="600" alt="Description">

<!-- With aspect ratio -->
<img data-src="/path/to/image.jpg" data-aspect-ratio="16/9" alt="Description">

<!-- High priority (loads immediately) -->
<img data-src="/path/to/image.jpg" data-priority="high" alt="Description">
```

#### Component Lazy Loading
```html
<!-- Lazy load heavy components -->
<div data-lazy-component="TicketChart"
     data-component-script="/js/components/ticket-chart.js"
     data-component-style="/css/components/ticket-chart.css"
     data-skeleton-type="card"
     data-min-height="300px">
</div>
```

#### Available Skeleton Types
- `default`: Basic lines and text
- `card`: Image + content layout
- `list`: Avatar + text items
- `table`: Table structure
- `spinner`: Loading spinner

### Critical CSS Optimization

#### Automatic Critical CSS Extraction
The system automatically:
1. Analyzes above-the-fold content (first 600px)
2. Extracts relevant CSS rules
3. Prioritizes by viewport position and layout impact
4. Inlines critical CSS for faster render

#### Manual Critical CSS Export
```javascript
// Export critical CSS for build optimization
const criticalData = window.exportCriticalCSS();
console.log('Critical CSS:', criticalData.css);
```

#### Font Optimization
Automatic font optimizations:
- Preloads critical fonts
- Adds `font-display: swap` 
- Prevents layout shift with fallback metrics

## 🎯 Performance Best Practices

### Images
✅ **Do:**
- Always include `width` and `height` attributes
- Use `data-aspect-ratio` for responsive images
- Mark above-the-fold images with `data-priority="high"`
- Use modern formats (WebP, AVIF) with fallbacks
- Optimize image sizes for different viewports

❌ **Don't:**
- Load images without dimensions
- Use massive images for thumbnails
- Skip alt text for accessibility

### CSS
✅ **Do:**
- Load critical CSS inline in `<head>`
- Use `rel="preload"` for important stylesheets
- Defer non-critical CSS (icons, admin styles)
- Minimize unused CSS
- Use CSS containment for isolated components

❌ **Don't:**
- Block render with large CSS files
- Import CSS inside CSS files (use build tools instead)
- Use inline styles excessively

### JavaScript
✅ **Do:**
- Defer non-critical scripts
- Use `async` for independent scripts
- Implement code splitting for large applications
- Lazy load heavy libraries
- Use service workers for caching

❌ **Don't:**
- Load heavy scripts synchronously
- Execute long-running tasks on main thread
- Skip compression and minification

### Layout Stability
✅ **Do:**
- Reserve space for dynamic content
- Use skeleton loaders
- Set explicit dimensions for media
- Use CSS transforms instead of layout-triggering properties
- Test with slow connections

❌ **Don't:**
- Insert content without reserved space
- Change element dimensions after load
- Use layout-triggering animations

## 📈 Performance Monitoring Dashboard

### Real-time Metrics
Monitor performance in real-time using browser console:
```javascript
// Get current metrics summary
window.getPerformanceMetrics();

// Force report metrics
window.reportPerformanceMetrics();

// Get CSS optimization status
window.getCSSOptimizationMetrics();
```

### Debug Tools

#### Performance Debug Mode
Add `?debug=performance` to any URL to enable:
- Real-time Core Web Vitals display
- Resource timing information
- Layout shift warnings
- Memory usage monitoring

#### Layout Shift Detection
Monitor layout shifts in real-time:
```javascript
// Listen for layout shift events
document.addEventListener('layoutshift', (event) => {
    console.log('Layout shift detected:', event.detail);
});
```

### Server-Side Analytics

#### Performance Metrics API
Set up endpoint to collect performance data:
```php
// Route: POST /api/performance-metrics
public function storeMetrics(Request $request) {
    PerformanceMetric::create([
        'url' => $request->input('url'),
        'lcp' => $request->input('metrics.LCP.value'),
        'fid' => $request->input('metrics.FID.value'),
        'cls' => $request->input('metrics.CLS.value'),
        'user_agent' => $request->input('userAgent'),
        'timestamp' => now(),
    ]);
}
```

#### Performance Alerts
Set up alerts for performance regressions:
```php
// Check for performance issues
if ($averageLCP > 3000) {
    // Alert: LCP regression detected
}

if ($averageCLS > 0.15) {
    // Alert: Layout shift issues
}
```

## 🔍 Troubleshooting Performance Issues

### High LCP (> 2.5s)
**Possible Causes:**
- Large images without optimization
- Slow server response time
- Render-blocking resources
- Poor font loading

**Solutions:**
- Optimize and compress images
- Use CDN for static assets
- Implement image lazy loading
- Preload critical resources
- Optimize server response time

### High CLS (> 0.1)
**Possible Causes:**
- Images without dimensions
- Dynamic content insertion
- Web fonts loading
- Ads or embeds

**Solutions:**
- Set image dimensions in HTML
- Reserve space for dynamic content
- Use font-display: swap
- Implement skeleton loaders
- Test with slow networks

### High FID (> 100ms)
**Possible Causes:**
- Heavy JavaScript execution
- Long tasks blocking main thread
- Third-party scripts
- Inefficient event handlers

**Solutions:**
- Break up long tasks
- Use web workers for heavy computation
- Defer non-critical JavaScript
- Optimize event handlers
- Implement code splitting

### Performance Monitoring Checklist

#### Initial Setup
- [ ] Performance monitor initialized
- [ ] Lazy loading system active
- [ ] Critical CSS optimizer running
- [ ] Image dimensions specified
- [ ] Font loading optimized

#### Regular Monitoring
- [ ] Check Core Web Vitals weekly
- [ ] Monitor slow resources
- [ ] Review layout shift reports
- [ ] Analyze long task patterns
- [ ] Validate mobile performance

#### Performance Testing
- [ ] Test on slow 3G connections
- [ ] Verify with different devices
- [ ] Check performance on different pages
- [ ] Test with JavaScript disabled
- [ ] Validate accessibility impact

## 🚀 Advanced Optimizations

### Service Worker Integration
```javascript
// Register performance-aware service worker
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').then(registration => {
        // Cache performance-critical resources
        registration.active.postMessage({
            type: 'CACHE_CRITICAL_RESOURCES',
            resources: ['/assets/css/critical.css', '/assets/js/app.js']
        });
    });
}
```

### Resource Hints
```html
<!-- Preload critical resources -->
<link rel="preload" href="/assets/css/critical.css" as="style">
<link rel="preload" href="/assets/fonts/primary.woff2" as="font" type="font/woff2" crossorigin>

<!-- Prefetch likely next pages -->
<link rel="prefetch" href="/dashboard">

<!-- DNS prefetch for external domains -->
<link rel="dns-prefetch" href="//fonts.googleapis.com">
```

### Performance Budgets
Set performance budgets in your development workflow or tooling:
```json
{
  "performanceBudgets": {
    "LCP": 2500,
    "FID": 100,
    "CLS": 0.1,
    "totalSize": "500KB",
    "imageSize": "200KB",
    "jsSize": "150KB",
    "cssSize": "50KB"
  }
}
```

## 📞 Support and Resources

### Performance Tools
- [Google PageSpeed Insights](https://pagespeed.web.dev/)
- [WebPageTest](https://www.webpagetest.org/)
- [Chrome DevTools](https://developer.chrome.com/docs/devtools/performance/)
- [Lighthouse CI](https://github.com/GoogleChrome/lighthouse-ci)

### Monitoring Services
- [Google Analytics](https://analytics.google.com/)
- [New Relic](https://newrelic.com/)
- [DataDog RUM](https://www.datadoghq.com/product/real-user-monitoring/)
- [SpeedCurve](https://speedcurve.com/)

### Documentation
- [Web Vitals Guide](https://web.dev/vitals/)
- [Performance Optimization](https://web.dev/fast/)
- [Image Optimization](https://web.dev/fast/#optimize-images)
- [CSS Performance](https://web.dev/fast/#optimize-css)

For performance questions or issues, check the browser console for real-time metrics and warnings.
