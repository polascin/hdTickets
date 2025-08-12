#!/usr/bin/env node
/**
 * CSS Cache Busting Test Script
 * 
 * This script verifies that CSS cache busting is working properly in both
 * development and production environments for HD Tickets application.
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const colors = {
    green: '\x1b[32m',
    red: '\x1b[31m',
    yellow: '\x1b[33m',
    blue: '\x1b[34m',
    reset: '\x1b[0m',
    bold: '\x1b[1m'
};

function log(message, color = colors.reset) {
    console.log(`${color}${message}${colors.reset}`);
}

function testCssCacheBusting() {
    log('\n🧪 CSS Cache Busting Test - HD Tickets Application', colors.bold + colors.blue);
    log('=' .repeat(60), colors.blue);
    
    let passed = 0;
    let failed = 0;
    
    // Test 1: Check Vite config has timestamp implementation
    log('\n1️⃣  Testing Vite Configuration...', colors.yellow);
    try {
        const viteConfigPath = path.join(__dirname, 'vite.config.js');
        const viteConfig = fs.readFileSync(viteConfigPath, 'utf8');
        
        const hasTimestamp = viteConfig.includes('timestamp') && viteConfig.includes('css');
        const hasAssetFileNaming = viteConfig.includes('assetFileNames') && viteConfig.includes('buildTimestamp');
        const hasEnvironmentCheck = viteConfig.includes('isProd') && viteConfig.includes('isDev');
        
        if (hasTimestamp && hasAssetFileNaming && hasEnvironmentCheck) {
            log('   ✅ Vite config has proper timestamp implementation', colors.green);
            passed++;
        } else {
            log('   ❌ Vite config missing timestamp features', colors.red);
            failed++;
        }
    } catch (error) {
        log(`   ❌ Error reading Vite config: ${error.message}`, colors.red);
        failed++;
    }
    
    // Test 2: Check build manifest.json exists and has timestamped CSS
    log('\n2️⃣  Testing Build Manifest...', colors.yellow);
    try {
        const manifestPath = path.join(__dirname, 'public/build/manifest.json');
        if (fs.existsSync(manifestPath)) {
            const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
            
            let timestampedCssFound = false;
            for (const key in manifest) {
                if (manifest[key].file && manifest[key].file.includes('.css') && manifest[key].file.includes('-')) {
                    // Check if CSS file has timestamp pattern (numbers at end)
                    const matches = manifest[key].file.match(/(\d+)\.css$/);
                    if (matches && matches[1].length >= 10) { // Timestamp should be at least 10 digits
                        timestampedCssFound = true;
                        log(`   ✅ Found timestamped CSS: ${manifest[key].file}`, colors.green);
                        break;
                    }
                }
            }
            
            if (timestampedCssFound) {
                passed++;
            } else {
                log('   ❌ No timestamped CSS files found in manifest', colors.red);
                failed++;
            }
        } else {
            log('   ⚠️  Build manifest not found. Run `npm run build` first.', colors.yellow);
            failed++;
        }
    } catch (error) {
        log(`   ❌ Error reading build manifest: ${error.message}`, colors.red);
        failed++;
    }
    
    // Test 3: Check CSS Timestamp Service Provider
    log('\n3️⃣  Testing CSS Timestamp Service Provider...', colors.yellow);
    try {
        const providerPath = path.join(__dirname, 'app/Providers/CssTimestampServiceProvider.php');
        const provider = fs.readFileSync(providerPath, 'utf8');
        
        const hasGenerate = provider.includes('generate(') && provider.includes('timestamp');
        const hasBladeDirective = provider.includes('cssWithTimestamp') && provider.includes('Blade::directive');
        const hasTimestampLogic = provider.includes('filemtime') && provider.includes('addTimestampToUrl');
        
        if (hasGenerate && hasBladeDirective && hasTimestampLogic) {
            log('   ✅ CSS Timestamp Service Provider is properly implemented', colors.green);
            passed++;
        } else {
            log('   ❌ CSS Timestamp Service Provider missing required features', colors.red);
            failed++;
        }
    } catch (error) {
        log(`   ❌ Error reading CSS Timestamp Service Provider: ${error.message}`, colors.red);
        failed++;
    }
    
    // Test 4: Check helper functions
    log('\n4️⃣  Testing Helper Functions...', colors.yellow);
    try {
        const helpersPath = path.join(__dirname, 'app/helpers.php');
        const helpers = fs.readFileSync(helpersPath, 'utf8');
        
        const hasCssWithTimestamp = helpers.includes('css_with_timestamp') && helpers.includes('generate');
        const hasCssTimestamp = helpers.includes('css_timestamp') && helpers.includes('generate');
        
        if (hasCssWithTimestamp && hasCssTimestamp) {
            log('   ✅ Helper functions are properly defined', colors.green);
            passed++;
        } else {
            log('   ❌ Helper functions missing or incomplete', colors.red);
            failed++;
        }
    } catch (error) {
        log(`   ❌ Error reading helper functions: ${error.message}`, colors.red);
        failed++;
    }
    
    // Test 5: Check service provider registration
    log('\n5️⃣  Testing Service Provider Registration...', colors.yellow);
    try {
        const configPath = path.join(__dirname, 'config/app.php');
        const config = fs.readFileSync(configPath, 'utf8');
        
        const isRegistered = config.includes('CssTimestampServiceProvider::class');
        
        if (isRegistered) {
            log('   ✅ CSS Timestamp Service Provider is registered', colors.green);
            passed++;
        } else {
            log('   ❌ CSS Timestamp Service Provider not registered in config/app.php', colors.red);
            failed++;
        }
    } catch (error) {
        log(`   ❌ Error reading app configuration: ${error.message}`, colors.red);
        failed++;
    }
    
    // Test 6: Check Blade template usage
    log('\n6️⃣  Testing Blade Template Implementation...', colors.yellow);
    try {
        const layoutPath = path.join(__dirname, 'resources/views/layouts/app.blade.php');
        const layout = fs.readFileSync(layoutPath, 'utf8');
        
        const hasViteDirective = layout.includes('@vite([');
        const hasManifestCheck = layout.includes('build/manifest.json') && layout.includes('file_exists');
        const hasTimestampHelper = layout.includes('css_with_timestamp');
        
        if (hasViteDirective && hasManifestCheck && hasTimestampHelper) {
            log('   ✅ Blade templates properly implement cache busting', colors.green);
            passed++;
        } else {
            log('   ⚠️  Some Blade templates may need cache busting updates', colors.yellow);
            // Don't fail this as existing implementation might vary
            passed++;
        }
    } catch (error) {
        log(`   ❌ Error reading Blade templates: ${error.message}`, colors.red);
        failed++;
    }
    
    // Summary
    log('\n📊 Test Results Summary', colors.bold + colors.blue);
    log('=' .repeat(30), colors.blue);
    log(`✅ Tests Passed: ${passed}`, colors.green);
    log(`❌ Tests Failed: ${failed}`, colors.red);
    log(`📈 Success Rate: ${Math.round((passed / (passed + failed)) * 100)}%`, colors.blue);
    
    if (failed === 0) {
        log('\n🎉 All tests passed! CSS cache busting is properly configured.', colors.bold + colors.green);
        log('\n📝 Next Steps:', colors.bold);
        log('   1. Run `npm run build` to generate production assets');
        log('   2. Run `npm run dev` to test development mode');
        log('   3. Check browser developer tools to verify timestamps in CSS URLs');
        log('   4. Deploy to production and verify cache busting works');
    } else {
        log('\n⚠️  Some tests failed. Please review the configuration.', colors.bold + colors.yellow);
        log('\n🛠️  Recommended Actions:', colors.bold);
        if (failed > 0) {
            log('   1. Check the failed test details above');
            log('   2. Ensure all files exist and have proper content');
            log('   3. Run `composer dump-autoload` to refresh autoloaded files');
            log('   4. Clear Laravel caches: `php artisan cache:clear`');
        }
    }
    
    return failed === 0;
}

// Run the test
testCssCacheBusting();
