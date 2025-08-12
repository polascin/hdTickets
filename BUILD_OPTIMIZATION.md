# Build Optimization Guide
## HD Tickets - Sports Event Monitoring System

This guide outlines the build optimization strategies implemented for the HD Tickets application, focusing on performance, caching, and efficient asset delivery.

## 🚀 Quick Start

### Development
```bash
npm run dev              # Start development server
npm run dev:host         # Development server accessible from network
npm run dev:full         # Development server + WebSocket server
npm run dev:https        # Development server with HTTPS
```

### Production Builds
```bash
npm run build            # Standard production build
npm run build:analyze    # Production build with bundle analysis
npm run build:report     # Production build with detailed report
npm run build:staging    # Staging environment build
```

### Build Analysis
```bash
npm run build:analyze    # Opens bundle analyzer in browser
npm run size:check       # Check asset sizes
npm run size:gzip        # Check compressed sizes
```

## 📊 Build Configuration

### Environment-Specific Settings

#### Development
- **Source Maps**: Inline for faster rebuilds
- **Minification**: Disabled for debugging
- **CSS Code Splitting**: Disabled for simplicity
- **Console Logs**: Preserved
- **HMR**: Enabled with overlay

#### Production
- **Source Maps**: Hidden (generated but not linked)
- **Minification**: Terser with 3 optimization passes
- **CSS Code Splitting**: Enabled for better caching
- **Console Logs**: Removed
- **Asset Optimization**: Full compression

#### Staging
- **Source Maps**: Visible for debugging
- **Minification**: Enabled with reduced passes
- **Console Logs**: Warnings preserved
- **Debug Info**: Available

### Code Splitting Strategy

The build process uses intelligent chunk splitting for optimal caching:

```javascript
// Vendor chunks
'vendor-vue'     // Vue ecosystem (Vue, Vue Router)
'vendor-charts'  // Chart.js and visualization libraries
'vendor-ui'      // UI components (SweetAlert2, Flatpickr, Heroicons)
'vendor-http'    // HTTP and WebSocket libraries
'vendor-alpine'  // Alpine.js ecosystem
'vendor'         // Other node_modules packages
```

### Asset Organization

```
public/build/
├── assets/
│   ├── css/[name]-[hash]-[timestamp].css    # CSS with cache busting
│   ├── js/[name]-[hash].js                  # JavaScript chunks
│   ├── images/[name]-[hash].[ext]           # Optimized images
│   └── fonts/[name]-[hash].[ext]            # Web fonts
├── bundle-analysis.html                      # Bundle analyzer report
├── build-report.html                        # Detailed build report
└── cache-manifest.json                      # Asset manifest
```

## 🎯 Performance Optimizations

### 1. CSS Cache Busting (CRITICAL REQUIREMENT)
- **Automatic Timestamp**: CSS files include build timestamps
- **Laravel Integration**: Timestamps available via `__CSS_TIMESTAMP__`
- **SCSS/Sass Support**: `$css-timestamp` variable injection
- **Cache Prevention**: Ensures fresh CSS delivery

### 2. Lazy Loading & Code Splitting
Utilize the lazy loading utilities in `resources/js/utils/lazyLoading.js`:

```javascript
import { lazyRoute, lazyComponent } from '@/utils/lazyLoading';

// Route-based lazy loading
const Dashboard = lazyRoute('@/pages/Dashboard.vue');

// Component lazy loading with error handling
const Chart = lazyComponent(() => import('@/components/Chart.vue'), {
    loading: LoadingSpinner,
    error: ErrorComponent,
    delay: 200,
    timeout: 10000
});
```

### 3. Asset Optimization
- **Images**: WebP/AVIF formats with fallbacks
- **Fonts**: WOFF2 with preloading for critical fonts
- **CSS**: esbuild minification (Tailwind CSS compatible)
- **JavaScript**: Terser with ES2022 output

### 4. Dependency Optimization
Pre-bundled dependencies for faster development:
- Vue ecosystem packages
- Chart.js components
- Alpine.js plugins
- UI libraries
- HTTP clients

## 📈 Bundle Analysis

### Running Analysis
```bash
# Generate treemap visualization
npm run build:analyze

# Generate detailed list report
npm run build:report
```

### Key Metrics to Monitor
- **Bundle Size**: Target < 1MB for main bundle
- **Chunk Count**: Optimal vendor splitting
- **Duplicate Dependencies**: Identify and eliminate
- **Unused Code**: Tree shaking effectiveness

### Performance Budgets
```javascript
maxAssetSize: 512 KB        // Individual asset limit
maxEntrypointSize: 1 MB     // Entry point bundle limit
maxAssets: 100              // Total asset count
```

## 🔧 Optimization Techniques

### 1. Tree Shaking
- ES modules for all imports
- Side-effect free packages marked
- Unused code elimination

### 2. Module Federation
- Shared vendor libraries
- Lazy loading of non-critical modules
- Dynamic imports for route components

### 3. Progressive Loading
```javascript
// Preload critical resources
import { preloadResources } from '@/utils/lazyLoading';

preloadResources([
    '/build/assets/css/app.css',
    '/build/assets/js/vendor-vue.js'
], 'script');
```

### 4. Image Optimization
```javascript
// Lazy image loading with fallbacks
import { lazyImage } from '@/utils/lazyLoading';

const optimizedSrc = await lazyImage('/images/hero.jpg', {
    placeholder: 'data:image/svg+xml;base64,...',
    fallback: '/images/placeholder.png'
});
```

## 🛠️ Development Tools

### Build Analysis Tools
- **Bundle Analyzer**: Visual representation of bundle composition
- **Build Reporter**: Detailed statistics and warnings
- **Performance Monitor**: Asset size tracking
- **Cache Inspector**: Cache busting verification

### Scripts for Monitoring
```bash
# Asset size monitoring
npm run size:check

# Compressed size analysis
npm run size:gzip

# Lighthouse performance testing
npm run performance:lighthouse
```

## 🔍 Debugging Builds

### Source Maps Configuration
- **Development**: Inline source maps for debugging
- **Production**: Hidden source maps (generated but not exposed)
- **Staging**: Visible source maps for issue investigation

### Build Debugging
```bash
# Debug mode build
npm run dev:debug

# Watch mode for incremental builds
npm run build:watch

# Clean build artifacts
npm run clean:all
```

## ⚡ Performance Best Practices

### 1. Critical Resource Priority
- Inline critical CSS
- Preload essential fonts
- Defer non-critical JavaScript

### 2. Caching Strategy
- Long-term caching for vendor chunks
- Content-based hashing for all assets
- Timestamp-based cache busting for CSS

### 3. Network Optimization
- Enable compression (gzip/brotli)
- Use CDN for static assets
- Implement service worker caching

### 4. Runtime Performance
- Lazy load route components
- Use Vue's async components
- Implement intersection observer for images

## 🎨 CSS Optimization

### Tailwind CSS Integration
- PostCSS processing with autoprefixer
- CSS nano compression in production
- Purge unused styles automatically
- Preserve dynamic class names

### Custom CSS Processing
```scss
// Timestamp injection (automatic)
$css-timestamp: #{$css-timestamp}; // Injected by build process

// Cache busting utility
.timestamp-aware {
    background-image: url('./image.jpg?t=#{$css-timestamp}');
}
```

## 🚦 Build Monitoring

### Automated Checks
- Bundle size warnings
- Performance budget enforcement
- Dependency audit
- Build time tracking

### Manual Verification
1. Check bundle analysis reports
2. Verify CSS timestamp inclusion
3. Test lazy loading functionality
4. Validate source map generation

## 🔧 Troubleshooting

### Common Issues
1. **CSS Not Updating**: Verify timestamp generation
2. **Large Bundle Size**: Check vendor chunk splitting
3. **Slow Build Times**: Review dependency optimization
4. **HMR Issues**: Check port accessibility (5173)

### Debug Commands
```bash
# Clear all caches
npm run clean:all

# Reinstall dependencies
rm -rf node_modules package-lock.json && npm install

# Force dependency re-optimization
npm run clean:cache && npm run dev
```

## 📚 Additional Resources

- [Vite Build Optimization Guide](https://vitejs.dev/guide/build.html)
- [Laravel Vite Integration](https://laravel.com/docs/vite)
- [Vue 3 Performance Guide](https://vuejs.org/guide/best-practices/performance.html)
- [Web Performance Metrics](https://web.dev/metrics/)

---

*This optimization guide is specific to the HD Tickets Sports Event Monitoring System and should be updated as the build configuration evolves.*
