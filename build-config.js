/**
 * Build Optimization Configuration
 * HD Tickets - Sports Event Monitoring System
 * 
 * Centralized build optimization settings for different environments
 * and performance monitoring utilities.
 */

const path = require('path');
const fs = require('fs');

// Build environment detection
const isDev = process.env.NODE_ENV === 'development';
const isProd = process.env.NODE_ENV === 'production';
const isAnalyze = process.env.ANALYZE === 'true';
const isReport = process.env.REPORT === 'true';

// Build optimization presets
const BUILD_PRESETS = {
    development: {
        sourcemap: 'inline',
        minify: false,
        cssCodeSplit: false,
        reportCompressedSize: false,
        chunkSizeWarningLimit: 2000,
        terser: {
            compress: {
                drop_console: false,
                drop_debugger: false,
                passes: 1
            }
        }
    },
    
    production: {
        sourcemap: 'hidden',
        minify: 'terser',
        cssCodeSplit: true,
        reportCompressedSize: true,
        chunkSizeWarningLimit: 1600,
        terser: {
            compress: {
                drop_console: true,
                drop_debugger: true,
                passes: 3,
                unsafe_arrows: true,
                unsafe_methods: true,
                reduce_vars: true,
                reduce_funcs: true
            },
            mangle: {
                properties: {
                    regex: /^_/
                },
                safari10: true
            }
        }
    },
    
    staging: {
        sourcemap: true,
        minify: 'terser',
        cssCodeSplit: true,
        reportCompressedSize: true,
        chunkSizeWarningLimit: 1800,
        terser: {
            compress: {
                drop_console: false,
                drop_debugger: true,
                passes: 2
            }
        }
    }
};

// Chunk splitting strategies
const CHUNK_STRATEGIES = {
    // Vendor chunk strategy for better caching
    vendor: (id) => {
        // Core framework libraries
        if (id.includes('vue') || id.includes('vue-router')) {
            return 'vendor-vue';
        }
        
        // UI and visualization libraries
        if (id.includes('chart.js') || id.includes('chartjs')) {
            return 'vendor-charts';
        }
        
        // UI components
        if (id.includes('sweetalert2') || id.includes('flatpickr') || 
            id.includes('@heroicons/vue') || id.includes('alpinejs')) {
            return 'vendor-ui';
        }
        
        // HTTP and WebSocket libraries
        if (id.includes('axios') || id.includes('laravel-echo') || 
            id.includes('pusher-js') || id.includes('socket.io-client')) {
            return 'vendor-http';
        }
        
        // Utility libraries
        if (id.includes('@vueuse') || id.includes('date-fns') || id.includes('zxcvbn')) {
            return 'vendor-utils';
        }
        
        // Inertia.js ecosystem
        if (id.includes('@inertiajs')) {
            return 'vendor-inertia';
        }
        
        // Other vendor libraries
        if (id.includes('node_modules')) {
            return 'vendor';
        }
    },
    
    // Route-based chunk strategy
    routes: (id) => {
        if (id.includes('pages/') || id.includes('views/')) {
            const segments = id.split('/');
            const pageSegment = segments.find(segment => segment.includes('pages') || segment.includes('views'));
            const index = segments.indexOf(pageSegment);
            if (index !== -1 && segments[index + 1]) {
                return `page-${segments[index + 1].split('.')[0]}`;
            }
        }
    }
};

// Asset optimization settings
const ASSET_OPTIMIZATION = {
    images: {
        formats: ['webp', 'avif', 'png', 'jpg'],
        quality: {
            webp: 80,
            avif: 75,
            png: 90,
            jpg: 85
        },
        sizes: [320, 640, 960, 1280, 1920],
        inlineLimit: 4096 // 4KB
    },
    
    fonts: {
        formats: ['woff2', 'woff'],
        preload: ['Inter-Regular.woff2', 'Inter-Medium.woff2', 'Inter-SemiBold.woff2'],
        display: 'swap'
    },
    
    css: {
        removeUnusedCSS: isProd,
        minify: isProd ? 'esbuild' : false,
        autoprefixer: {
            browsers: ['> 1%', 'last 2 versions', 'not dead', 'not ie 11']
        }
    }
};

// Performance budgets
const PERFORMANCE_BUDGETS = {
    // Byte-based budgets
    maxAssetSize: 512 * 1024, // 512KB
    maxEntrypointSize: 1024 * 1024, // 1MB
    
    // Count-based budgets
    maxAssets: 100,
    maxEntrypoints: 5,
    
    // Time-based budgets (for Lighthouse)
    firstContentfulPaint: 1500, // 1.5s
    largestContentfulPaint: 2500, // 2.5s
    firstInputDelay: 100, // 100ms
    cumulativeLayoutShift: 0.1
};

// Build analysis utilities
class BuildAnalyzer {
    constructor() {
        this.startTime = Date.now();
        this.stats = {
            chunks: {},
            assets: {},
            warnings: [],
            errors: []
        };
    }
    
    // Analyze build output
    analyze(buildStats) {
        const endTime = Date.now();
        const buildTime = endTime - this.startTime;
        
        console.log(`\n📊 Build Analysis Report`);
        console.log(`⏱️  Build time: ${buildTime}ms`);
        
        if (buildStats.assets) {
            this.analyzeAssets(buildStats.assets);
        }
        
        if (buildStats.chunks) {
            this.analyzeChunks(buildStats.chunks);
        }
        
        this.checkPerformanceBudgets(buildStats);
        
        return {
            buildTime,
            stats: this.stats
        };
    }
    
    // Analyze asset sizes
    analyzeAssets(assets) {
        console.log(`\n📦 Assets:`);
        
        assets.forEach(asset => {
            const size = this.formatFileSize(asset.size);
            console.log(`   ${asset.name}: ${size}`);
            
            // Check against budgets
            if (asset.size > PERFORMANCE_BUDGETS.maxAssetSize) {
                this.stats.warnings.push(`Asset ${asset.name} exceeds size budget (${size})`);
            }
        });
    }
    
    // Analyze chunk sizes
    analyzeChunks(chunks) {
        console.log(`\n🧩 Chunks:`);
        
        chunks.forEach(chunk => {
            const size = this.formatFileSize(chunk.size);
            console.log(`   ${chunk.name}: ${size}`);
            
            this.stats.chunks[chunk.name] = chunk.size;
        });
    }
    
    // Check performance budgets
    checkPerformanceBudgets(buildStats) {
        console.log(`\n⚡ Performance Budget Check:`);
        
        let passed = 0;
        let failed = 0;
        
        // Check asset count
        const assetCount = buildStats.assets ? buildStats.assets.length : 0;
        if (assetCount <= PERFORMANCE_BUDGETS.maxAssets) {
            console.log(`   ✅ Asset count: ${assetCount}/${PERFORMANCE_BUDGETS.maxAssets}`);
            passed++;
        } else {
            console.log(`   ❌ Asset count: ${assetCount}/${PERFORMANCE_BUDGETS.maxAssets}`);
            failed++;
        }
        
        // Check entrypoint size
        if (buildStats.entrypoints) {
            Object.entries(buildStats.entrypoints).forEach(([name, entrypoint]) => {
                const totalSize = entrypoint.assets.reduce((total, asset) => total + asset.size, 0);
                const formatted = this.formatFileSize(totalSize);
                
                if (totalSize <= PERFORMANCE_BUDGETS.maxEntrypointSize) {
                    console.log(`   ✅ Entrypoint ${name}: ${formatted}`);
                    passed++;
                } else {
                    console.log(`   ❌ Entrypoint ${name}: ${formatted} (exceeds budget)`);
                    failed++;
                }
            });
        }
        
        console.log(`\n🎯 Budget Summary: ${passed} passed, ${failed} failed`);
    }
    
    // Format file size for display
    formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Generate detailed report
    generateReport() {
        const reportPath = path.resolve('public/build/build-report.json');
        
        try {
            fs.writeFileSync(reportPath, JSON.stringify({
                timestamp: new Date().toISOString(),
                environment: process.env.NODE_ENV,
                stats: this.stats,
                budgets: PERFORMANCE_BUDGETS
            }, null, 2));
            
            console.log(`📋 Detailed report saved to: ${reportPath}`);
        } catch (error) {
            console.error('Failed to generate report:', error);
        }
    }
}

// Cache busting utilities
const CacheBusting = {
    // Generate cache-busting timestamp
    generateTimestamp() {
        return Date.now();
    },
    
    // Add timestamp to CSS files (as per requirements)
    addCSSTimestamp(filename, timestamp) {
        const parts = filename.split('.');
        const ext = parts.pop();
        const name = parts.join('.');
        return `${name}-${timestamp}.${ext}`;
    },
    
    // Create cache manifest
    createManifest(assets, outputPath) {
        const manifest = {};
        
        assets.forEach(asset => {
            const originalName = asset.name.replace(/-\d+\./, '.');
            manifest[originalName] = asset.name;
        });
        
        fs.writeFileSync(
            path.join(outputPath, 'cache-manifest.json'),
            JSON.stringify(manifest, null, 2)
        );
    }
};

// Export configuration and utilities
module.exports = {
    BUILD_PRESETS,
    CHUNK_STRATEGIES,
    ASSET_OPTIMIZATION,
    PERFORMANCE_BUDGETS,
    BuildAnalyzer,
    CacheBusting,
    
    // Environment flags
    isDev,
    isProd,
    isAnalyze,
    isReport,
    
    // Helper functions
    getCurrentPreset() {
        const mode = process.env.NODE_ENV || 'development';
        return BUILD_PRESETS[mode] || BUILD_PRESETS.development;
    },
    
    getChunkStrategy(strategy = 'vendor') {
        return CHUNK_STRATEGIES[strategy] || CHUNK_STRATEGIES.vendor;
    }
};
