# HD Tickets - Comprehensive Dependency Audit Report
*Generated: $(date)*

## Executive Summary

This report provides a comprehensive audit of both PHP Composer and Node.js npm dependencies for the HD Tickets - Sports Event Ticket Monitoring System.

### System Environment
- **Platform**: Ubuntu 24.04 LTS
- **Web Server**: Apache2
- **PHP Version**: PHP 8.4.11
- **Database**: MySQL/MariaDB 10.4
- **Composer Version**: 2.8.10
- **Node.js**: >= 18.0.0 (as specified in package.json)
- **npm**: >= 9.0.0 (as specified in package.json)

---

## 1. PHP Composer Dependencies Audit

### 1.1 Composer Configuration Health
✅ **composer.json**: OK
✅ **composer.lock**: OK  
✅ **Platform settings**: OK
✅ **Git settings**: OK
✅ **Connectivity**: OK (packagist http/https)
✅ **GitHub rate limit**: OK
✅ **Disk space**: OK
✅ **Public keys**: OK
✅ **Composer version**: OK
✅ **Security vulnerabilities**: OK

### 1.2 Current PHP Dependencies (composer.json)

#### Production Dependencies
- **php**: ^8.4
- **laravel/framework**: ^12.0
- **laravel/tinker**: ^2.9
- **laravel/sanctum**: ^4.0
- **laravel/passport**: ^13.0
- **guzzlehttp/guzzle**: ^7.0
- **symfony/dom-crawler**: ^7.0
- **symfony/css-selector**: ^7.0
- **barryvdh/laravel-dompdf**: ^3.0
- **spatie/laravel-activitylog**: ^4.8
- **twilio/sdk**: ^8.7
- **pusher/pusher-php-server**: ^7.2
- **predis/predis**: ^3.1
- **roach-php/laravel**: ^3.0
- **spatie/browsershot**: ^5.0.5
- **pragmarx/google2fa**: ^8.0
- **pragmarx/google2fa-qrcode**: ^3.0
- **stripe/stripe-php**: ^17.4
- **laravel/slack-notification-channel**: ^3.6
- **paypal/paypal-server-sdk**: ^1.1
- **laravel/horizon**: ^5.33
- **intervention/image**: ^3.0
- **jenssegers/agent**: ^2.6
- **maatwebsite/excel**: ^3.1
- **phpoffice/phpspreadsheet**: ^1.30

#### Development Dependencies
- **laravel/breeze**: ^2.3
- **phpunit/phpunit**: ^12.0
- **mockery/mockery**: ^1.6
- **fakerphp/faker**: ^1.24
- **laravel/pint**: ^1.0
- **nunomaduro/collision**: ^8.4
- **larastan/larastan**: ^3.0
- **spatie/laravel-ignition**: ^2.8
- **symfony/var-dumper**: ^7.3
- **laravel/telescope**: ^5.10
- **brianium/paratest**: ^7.8
- **friendsofphp/php-cs-fixer**: ^3.64
- **phpstan/phpstan**: ^2.0
- **phpmd/phpmd**: ^2.15
- **squizlabs/php_codesniffer**: ^3.10
- **phpmetrics/phpmetrics**: ^2.9
- **rector/rector**: ^2.0

### 1.3 Outdated PHP Packages

#### Direct Dependencies
- ⚠️ **phpoffice/phpspreadsheet**: 1.30.0 → 5.0.0 (MAJOR update available)

#### Transitive Dependencies
- ⚠️ **league/container**: 4.2.5 → 5.1.0 (MAJOR update available)
- ⚠️ **mobiledetect/mobiledetectlib**: 2.8.45 → 4.8.09 (MAJOR update available)
- ⚠️ **php-jsonpointer/php-jsonpointer**: 3.0.2 → 4.0.0 (MAJOR update available)
- ⚠️ **sabberworm/php-css-parser**: 8.9.0 → 9.0.0 (MAJOR update available)
- ⚠️ **spatie/error-solutions**: 1.1.3 → 2.0.1 (MAJOR update available)
- ⚠️ **spatie/flare-client-php**: 1.10.1 → 2.0.7 (MAJOR update available)

---

## 2. Node.js npm Dependencies Audit

### 2.1 Current Node.js Dependencies (package.json)

#### Production Dependencies
- **@alpinejs/collapse**: ^3.14.9
- **@alpinejs/focus**: ^3.14.9
- **@alpinejs/intersect**: ^3.14.9
- **@alpinejs/persist**: ^3.14.9
- **@floating-ui/vue**: ^1.0.6
- **@headlessui/vue**: ^1.7.16
- **@heroicons/vue**: ^2.0.18
- **@tanstack/vue-query**: ^5.17.19
- **@vue/runtime-dom**: ^3.4.15
- **@vueuse/components**: ^10.7.2
- **@vueuse/core**: ^10.7.2
- **@vueuse/motion**: ^2.0.0
- **alpinejs**: ^3.14.9
- **axios**: ^1.6.7
- **chart.js**: ^4.4.1
- **chartjs-adapter-date-fns**: ^3.0.0
- **date-fns**: ^3.3.1
- **framer-motion**: ^11.0.5
- **fuse.js**: ^7.0.0
- **lodash-es**: ^4.17.21
- **mitt**: ^3.0.1
- **pinia**: ^2.1.7
- **socket.io-client**: ^4.7.4
- **sortablejs**: ^1.15.2
- **virtual-keyboard**: ^1.30.4
- **vue**: ^3.4.15
- **vue-router**: ^4.2.5
- **zod**: ^3.22.4

#### Development Dependencies
- **@types/node**: ^20.11.16
- **@typescript-eslint/eslint-plugin**: ^6.20.0
- **@typescript-eslint/parser**: ^6.20.0
- **@vitejs/plugin-legacy**: ^5.3.0
- **@vitejs/plugin-vue**: ^5.0.3
- **@vue/compiler-sfc**: ^3.4.15
- **@vue/test-utils**: ^2.4.4
- **autoprefixer**: ^10.4.17
- **eslint**: ^8.56.0
- **eslint-plugin-vue**: ^9.20.1
- **eslint-plugin-vuejs-accessibility**: ^2.4.1
- **jsdom**: ^24.0.0
- **postcss**: ^8.4.35
- **prettier**: ^3.2.5
- **rollup-plugin-visualizer**: ^5.12.0
- **terser**: ^5.27.0
- **typescript**: ^5.3.3
- **vite**: ^5.4.8
- **vite-plugin-eslint**: ^1.8.1
- **vite-plugin-pwa**: ^0.17.5
- **vite-plugin-windicss**: ^1.9.3
- **vitest**: ^2.1.1
- **vue-tsc**: ^2.1.6
- **windicss**: ^3.5.6
- **workbox-cli**: ^7.0.0
- **workbox-webpack-plugin**: ^7.0.0

### 2.2 Outdated Node.js Packages

#### Major Updates Available
- ⚠️ **@types/node**: 20.19.10 → 24.2.1
- ⚠️ **@typescript-eslint/eslint-plugin**: 6.21.0 → 8.39.1
- ⚠️ **@typescript-eslint/parser**: 6.21.0 → 8.39.1
- ⚠️ **@vitejs/plugin-legacy**: 5.4.3 → 7.2.1
- ⚠️ **@vitejs/plugin-vue**: 5.2.4 → 6.0.1
- ⚠️ **@vueuse/components**: 10.11.1 → 13.6.0
- ⚠️ **@vueuse/core**: 10.11.1 → 13.6.0
- ⚠️ **@vueuse/motion**: 2.2.6 → 3.0.3
- ⚠️ **date-fns**: 3.6.0 → 4.1.0
- ⚠️ **eslint**: 8.57.1 → 9.33.0
- ⚠️ **eslint-plugin-vue**: 9.33.0 → 10.4.0
- ⚠️ **framer-motion**: 11.18.2 → 12.23.12
- ⚠️ **jsdom**: 24.1.3 → 26.1.0
- ⚠️ **pinia**: 2.3.1 → 3.0.3
- ⚠️ **rollup-plugin-visualizer**: 5.14.0 → 6.0.3
- ⚠️ **vite**: 5.4.19 → 7.1.2
- ⚠️ **vite-plugin-pwa**: 0.17.5 → 1.0.2
- ⚠️ **vitest**: 2.1.9 → 3.2.4
- ⚠️ **vue-tsc**: 2.2.12 → 3.0.5
- ⚠️ **zod**: 3.25.76 → 4.0.17

### 2.3 Security Vulnerabilities (npm audit)

**15 vulnerabilities found: 3 low, 12 moderate**

#### Critical Security Issues:

1. **esbuild <=0.24.2 (Moderate)**
   - Issue: Enables any website to send requests to development server
   - Advisory: GHSA-67mh-4wv8-2f99
   - Affected: vite, @vitejs/plugin-legacy, @vitest/mocker, vitest, vite-node, vite-plugin-pwa

2. **got <11.8.5 (Moderate)**
   - Issue: Allows redirect to UNIX socket
   - Advisory: GHSA-pfrx-2q88-qq97
   - Affected: package-json, latest-version, update-notifier, workbox-cli

3. **tmp <=0.2.3**
   - Issue: Allows arbitrary temporary file/directory write via symbolic link
   - Advisory: GHSA-52f5-9888-hmc6
   - Affected: external-editor, inquirer

---

## 3. Dependency Tracking Analysis

### 3.1 Lock Files Status
✅ **composer.lock**: Present and valid
✅ **package-lock.json**: Present and valid

### 3.2 Dependency Directories
✅ **vendor/**: Present (PHP dependencies)
✅ **node_modules/**: Present (Node.js dependencies)

### 3.3 Manual Dependencies Check
No manually installed dependencies found outside of package managers.
All dependencies are properly tracked in configuration files.

---

## 4. Recommendations

### 4.1 High Priority Actions

1. **Address Security Vulnerabilities**
   - Run `npm audit fix` to automatically fix compatible vulnerabilities
   - Consider `npm audit fix --force` for breaking changes after thorough testing

2. **Update Critical Dependencies**
   - Review and plan major version updates for core packages
   - Test compatibility before updating production dependencies

3. **PHP Dependency Updates**
   - Evaluate phpoffice/phpspreadsheet major update (1.30.0 → 5.0.0)
   - Review breaking changes in transitive dependencies

### 4.2 Medium Priority Actions

1. **TypeScript/ESLint Ecosystem Update**
   - Plan migration from TypeScript ESLint v6 to v8
   - Update Vue 3 ecosystem packages (@vueuse, etc.)

2. **Build Tool Updates**
   - Evaluate Vite upgrade path (5.4.19 → 7.1.2)
   - Update Vitest for better testing experience

### 4.3 Low Priority Actions

1. **Node.js Version Compatibility**
   - Consider updating @types/node for better type support
   - Review Node.js LTS compatibility

2. **Development Experience**
   - Update development tools for better DX
   - Consider migrating to newer linting configurations

---

## 5. Next Steps

1. **Immediate**: Address security vulnerabilities
2. **Short-term**: Plan testing strategy for major updates
3. **Medium-term**: Execute controlled dependency updates
4. **Long-term**: Establish regular dependency maintenance schedule

---

*Report generated on $(date) for HD Tickets v5.0.0*
*System: Ubuntu 24.04 LTS, Apache2, PHP 8.4, MySQL/MariaDB 10.4*
