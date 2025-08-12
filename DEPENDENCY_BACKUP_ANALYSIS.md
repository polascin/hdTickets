# HD Tickets Dependency Analysis & Backup Documentation

**Date:** 2025-08-12 06:16:29
**Purpose:** Pre-upgrade dependency analysis and backup documentation  
**Project:** HD Tickets - Sports Event Ticket Monitoring System  

## Current System Environment

- **OS:** Ubuntu 24.04 LTS
- **Web Server:** Apache2
- **Project Location:** /var/www/hdtickets

## Laravel Framework

### Current Version
- **Laravel Framework:** 12.22.1 (checked via `php artisan --version`)
- **PHP Requirement:** ^8.4 (specified in composer.json)

### Core Laravel Dependencies (from composer.json)
```json
{
    "laravel/framework": "^12.0",
    "laravel/tinker": "^2.9",
    "laravel/sanctum": "^4.0", 
    "laravel/passport": "^12.0",
    "laravel/breeze": "^2.3",
    "laravel/horizon": "^5.33",
    "laravel/slack-notification-channel": "^3.6",
    "laravel/pint": "^1.0",
    "laravel/telescope": "^5.10"
}
```

## Frontend Dependencies

### Vite.js Configuration
- **Version:** ^6.3.5 (from package.json)
- **Configuration File:** vite.config.js
- **Key Features:**
  - Vue 3 support with @vitejs/plugin-vue ^5.2.1
  - CSS cache busting with timestamp implementation
  - Advanced chunk splitting strategy
  - Terser minification for production
  - LightningCSS for CSS optimization
  - Modern ES2022 target

### Alpine.js
- **Main Package:** ^3.14.9
- **Additional Alpine Plugins:**
  - @alpinejs/collapse: ^3.14.9
  - @alpinejs/focus: ^3.14.9
  - @alpinejs/intersect: ^3.14.9
  - @alpinejs/persist: ^3.14.9

### Vue.js Ecosystem
- **Vue.js:** ^3.3.11
- **Vue Router:** ^4.2.5
- **@heroicons/vue:** ^2.0.18
- **@inertiajs/vue3:** ^1.0.14
- **@vueuse/core:** ^10.9.0
- **@vueuse/components:** ^10.9.0

### UI & Styling
- **Bootstrap:** ^5.3.2
- **Tailwind CSS:** ^3.4.17
- **@tailwindcss/forms:** ^0.5.10
- **@tailwindcss/typography:** ^0.5.15

### Other Frontend Dependencies
- **Axios:** ^1.6.2
- **Chart.js:** ^4.4.1 (with chartjs-adapter-date-fns ^3.0.0)
- **SweetAlert2:** 11.4.8 (pinned version)
- **Flatpickr:** ^4.6.13
- **Laravel Echo:** ^1.19.0
- **Pusher.js:** ^8.4.0
- **Socket.io Client:** ^4.7.4

## Backend Dependencies

### Core Libraries
- **Guzzle HTTP:** ^7.0
- **Symfony Components:**
  - symfony/dom-crawler: ^7.0
  - symfony/css-selector: ^7.0
  - symfony/var-dumper: ^7.3

### Specialized Packages
- **PDF Generation:** barryvdh/laravel-dompdf ^3.0
- **Activity Logging:** spatie/laravel-activitylog ^4.8
- **Web Scraping:** roach-php/laravel ^3.0
- **Browser Automation:** spatie/browsershot ^5.0.5
- **Image Processing:** intervention/image ^3.0
- **Device Detection:** jenssegers/agent ^2.6

### Communication & Notifications
- **Twilio SDK:** ^8.7
- **Pusher PHP Server:** ^7.2
- **Redis Client:** predis/predis ^3.1

### Authentication & Security
- **Google 2FA:** pragmarx/google2fa ^8.0
- **Google 2FA QR:** pragmarx/google2fa-qrcode ^3.0

### Payment Processing
- **Stripe:** stripe/stripe-php ^17.4
- **PayPal:** paypal/paypal-server-sdk ^1.1

### Development Dependencies
- **Testing:** phpunit/phpunit ^11.0
- **Code Quality:** larastan/larastan ^3.0
- **Debugging:** spatie/laravel-ignition ^2.8
- **Parallel Testing:** brianium/paratest ^7.8
- **Mocking:** mockery/mockery ^1.6
- **Fake Data:** fakerphp/faker ^1.24

## Database Configuration
- **Database:** MySQL
- **Host:** 127.0.0.1:3306
- **Database Name:** hdtickets
- **Username:** hdtickets

## Build Tools & Development
- **CSS Processor:** PostCSS ^8.4.49 with autoprefixer ^10.4.16
- **CSS Minification:** cssnano ^7.0.7, lightningcss ^1.30.1
- **JS Minification:** terser ^5.43.1
- **Sass Support:** sass ^1.83.0
- **WebSocket Server:** @soketi/soketi ^1.6.1

## Lock Files Present
- composer.lock (PHP dependencies)
- package-lock.json (Node.js dependencies)

## Special Configuration Notes

### CSS Cache Busting
The application implements timestamp-based CSS cache busting as specified in user rules:
- Vite config generates timestamp: `Date.now()`
- CSS files include timestamp in filename
- Global `__CSS_TIMESTAMP__` variable available

### Custom Aliases (Vite)
- '@': resources/js
- '@components': resources/js/components  
- '@modules': resources/js/modules
- '@utils': resources/js/utils

### Chunk Splitting Strategy
- vendor-vue: Vue ecosystem
- vendor-charts: Chart.js libraries
- vendor-ui: UI components
- vendor-http: HTTP/WebSocket libraries
- vendor-alpine: Alpine.js ecosystem
- vendor: Other node_modules

## Backup Requirements
1. **Source Code:** Complete project directory
2. **Database:** MySQL hdtickets database
3. **Configuration:** .env file and other config files
4. **Dependencies:** composer.lock and package-lock.json
5. **User Uploads:** storage/app directory
6. **Logs:** storage/logs directory

## Rollback Preparation
All dependency versions documented above should be preserved in case rollback is needed during the upgrade process. The composer.lock and package-lock.json files contain the exact versions currently installed.
