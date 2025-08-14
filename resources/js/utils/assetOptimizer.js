/**
 * HD Tickets - Asset Optimization Utilities
 * Handles CSS minification, lazy loading, and asset optimization
 */

class AssetOptimizer {
  constructor() {
    this.criticalCSSLoaded = false;
    this.nonCriticalCSSLoaded = false;
    this.assetsCache = new Map();
    this.imageObserver = null;
    this.preloadedAssets = new Set();
  }

  /**
   * Initialize asset optimization
   */
  init() {
    this.setupImageLazyLoading();
    this.optimizeFonts();
    this.setupAssetPreloading();
    this.monitorPerformance();
  }

  /**
   * Lazy load non-critical CSS after page load
   */
  loadNonCriticalCSS() {
    if (this.nonCriticalCSSLoaded) return;

    const nonCriticalStyles = [
      '/css/components/hd-backgrounds.css',
      '/css/components/hd-skeleton.css',
      '/css/utils/hd-utils.css'
    ];

    const loadCSS = (href) => {
      return new Promise((resolve, reject) => {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href + '?v=' + Date.now();
        link.onload = resolve;
        link.onerror = reject;
        document.head.appendChild(link);
      });
    };

    // Load non-critical CSS after DOM is ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => {
        Promise.all(nonCriticalStyles.map(loadCSS))
          .then(() => {
            this.nonCriticalCSSLoaded = true;
            console.log('✅ Non-critical CSS loaded');
          })
          .catch(err => console.warn('⚠️ Error loading non-critical CSS:', err));
      });
    } else {
      Promise.all(nonCriticalStyles.map(loadCSS))
        .then(() => {
          this.nonCriticalCSSLoaded = true;
          console.log('✅ Non-critical CSS loaded');
        })
        .catch(err => console.warn('⚠️ Error loading non-critical CSS:', err));
    }
  }

  /**
   * Setup lazy loading for images
   */
  setupImageLazyLoading() {
    if ('IntersectionObserver' in window) {
      this.imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            this.loadImage(img);
            this.imageObserver.unobserve(img);
          }
        });
      }, {
        rootMargin: '50px 0px',
        threshold: 0.01
      });

      // Observe all lazy images
      document.querySelectorAll('img[data-src]').forEach(img => {
        this.imageObserver.observe(img);
      });
    } else {
      // Fallback for older browsers
      document.querySelectorAll('img[data-src]').forEach(img => {
        this.loadImage(img);
      });
    }
  }

  /**
   * Load individual image with optimization
   */
  loadImage(img) {
    const src = img.dataset.src;
    const srcset = img.dataset.srcset;
    
    if (!src) return;

    // Create a new image to preload
    const newImg = new Image();
    
    newImg.onload = () => {
      // Add fade-in animation
      img.style.transition = 'opacity 0.3s ease';
      img.style.opacity = '0';
      
      if (srcset) img.srcset = srcset;
      img.src = src;
      
      // Remove data attributes
      delete img.dataset.src;
      delete img.dataset.srcset;
      
      // Fade in
      requestAnimationFrame(() => {
        img.style.opacity = '1';
      });
      
      // Remove skeleton if present
      const skeleton = img.parentElement.querySelector('.hd-skeleton--logo');
      if (skeleton) {
        skeleton.style.display = 'none';
      }
    };

    newImg.onerror = () => {
      console.warn('Failed to load image:', src);
      // Show fallback or error state
      img.alt = 'Image failed to load';
    };

    if (srcset) newImg.srcset = srcset;
    newImg.src = src;
  }

  /**
   * Optimize font loading
   */
  optimizeFonts() {
    // Preload critical fonts
    const criticalFonts = [
      'https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap'
    ];

    criticalFonts.forEach(font => {
      const link = document.createElement('link');
      link.rel = 'preload';
      link.as = 'style';
      link.href = font;
      link.onload = () => {
        link.rel = 'stylesheet';
      };
      document.head.appendChild(link);
    });
  }

  /**
   * Setup asset preloading for critical resources
   */
  setupAssetPreloading() {
    const criticalAssets = [
      '/assets/images/hdTicketsLogo.webp',
      '/assets/images/hdTicketsLogo.png'
    ];

    criticalAssets.forEach(asset => {
      if (this.preloadedAssets.has(asset)) return;

      const link = document.createElement('link');
      link.rel = 'preload';
      
      if (asset.includes('.webp') || asset.includes('.png') || asset.includes('.jpg')) {
        link.as = 'image';
      }
      
      link.href = asset;
      document.head.appendChild(link);
      this.preloadedAssets.add(asset);
    });
  }

  /**
   * Minify inline CSS (for development)
   */
  minifyInlineCSS() {
    if (process.env.NODE_ENV === 'production') return;

    const styleElements = document.querySelectorAll('style');
    
    styleElements.forEach(style => {
      if (style.dataset.minified) return;
      
      let css = style.textContent;
      
      // Basic CSS minification
      css = css
        .replace(/\/\*[\s\S]*?\*\//g, '') // Remove comments
        .replace(/\s+/g, ' ') // Collapse whitespace
        .replace(/;\s*}/g, '}') // Remove last semicolon before closing brace
        .replace(/\s*{\s*/g, '{') // Clean up around braces
        .replace(/\s*}\s*/g, '}')
        .replace(/\s*,\s*/g, ',') // Clean up commas
        .replace(/\s*:\s*/g, ':') // Clean up colons
        .replace(/\s*;\s*/g, ';') // Clean up semicolons
        .trim();
      
      style.textContent = css;
      style.dataset.minified = 'true';
    });
  }

  /**
   * Monitor performance metrics
   */
  monitorPerformance() {
    if ('PerformanceObserver' in window) {
      // Monitor Largest Contentful Paint
      const lcpObserver = new PerformanceObserver((entryList) => {
        const lcpEntries = entryList.getEntries();
        const lastLCPEntry = lcpEntries[lcpEntries.length - 1];
        
        if (lastLCPEntry.startTime < 2500) {
          console.log('✅ Good LCP:', lastLCPEntry.startTime, 'ms');
        } else {
          console.warn('⚠️ Poor LCP:', lastLCPEntry.startTime, 'ms');
        }
      });

      lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });

      // Monitor Cumulative Layout Shift
      let clsValue = 0;
      const clsObserver = new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
          if (!entry.hadRecentInput) {
            clsValue += entry.value;
          }
        }
        
        if (clsValue < 0.1) {
          console.log('✅ Good CLS:', clsValue);
        } else {
          console.warn('⚠️ Poor CLS:', clsValue);
        }
      });

      clsObserver.observe({ entryTypes: ['layout-shift'] });
    }
  }

  /**
   * Add cache headers for assets (client-side cache management)
   */
  setupClientSideCaching() {
    // Cache critical CSS in localStorage for faster subsequent loads
    const criticalCSS = document.querySelector('style');
    if (criticalCSS && criticalCSS.textContent.length > 0) {
      const cssHash = this.hashCode(criticalCSS.textContent);
      const storedHash = localStorage.getItem('hd-critical-css-hash');
      
      if (storedHash !== cssHash.toString()) {
        localStorage.setItem('hd-critical-css', criticalCSS.textContent);
        localStorage.setItem('hd-critical-css-hash', cssHash.toString());
      }
    }
  }

  /**
   * Simple hash function for cache busting
   */
  hashCode(str) {
    let hash = 0;
    if (str.length === 0) return hash;
    for (let i = 0; i < str.length; i++) {
      const char = str.charCodeAt(i);
      hash = ((hash << 5) - hash) + char;
      hash = hash & hash; // Convert to 32bit integer
    }
    return hash;
  }

  /**
   * Create WebP images with PNG fallback
   */
  convertToWebP(imgElement) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    canvas.width = imgElement.naturalWidth;
    canvas.height = imgElement.naturalHeight;
    
    ctx.drawImage(imgElement, 0, 0);
    
    // Try to create WebP
    const webpDataURL = canvas.toDataURL('image/webp', 0.8);
    
    // Check if WebP is supported (WebP data URLs are longer than PNG)
    if (webpDataURL.length < canvas.toDataURL('image/png').length * 0.8) {
      return webpDataURL;
    }
    
    return null; // WebP not supported or not beneficial
  }

  /**
   * Optimize image delivery based on connection
   */
  optimizeForConnection() {
    if ('connection' in navigator) {
      const connection = navigator.connection;
      
      if (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g') {
        // Use smaller images for slow connections
        document.documentElement.classList.add('slow-connection');
      } else if (connection.effectiveType === '4g') {
        // Preload more images for fast connections
        document.documentElement.classList.add('fast-connection');
        this.preloadMoreAssets();
      }
    }
  }

  /**
   * Preload additional assets for fast connections
   */
  preloadMoreAssets() {
    const additionalAssets = [
      '/css/mobile-enhancements.css',
      '/js/auth-security.js'
    ];

    additionalAssets.forEach(asset => {
      if (this.preloadedAssets.has(asset)) return;

      const link = document.createElement('link');
      link.rel = 'preload';
      
      if (asset.endsWith('.css')) {
        link.as = 'style';
      } else if (asset.endsWith('.js')) {
        link.as = 'script';
      }
      
      link.href = asset;
      document.head.appendChild(link);
      this.preloadedAssets.add(asset);
    });
  }
}

// Initialize asset optimizer when DOM is ready
const assetOptimizer = new AssetOptimizer();

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    assetOptimizer.init();
    assetOptimizer.loadNonCriticalCSS();
    assetOptimizer.optimizeForConnection();
  });
} else {
  assetOptimizer.init();
  assetOptimizer.loadNonCriticalCSS();
  assetOptimizer.optimizeForConnection();
}

// Load non-critical CSS after window load for better performance
window.addEventListener('load', () => {
  assetOptimizer.setupClientSideCaching();
  assetOptimizer.minifyInlineCSS();
});

export default assetOptimizer;
