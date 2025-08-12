# Dependency Configuration Backup

## Backup Information
- **Date**: 2025-08-13 00:51:03
- **Branch**: main
- **Commit**: 85acdb8 (feat: fix type annotations in ViagogoClient and TicketScrapingService)
- **Project**: HD Tickets - Sports Events Entry Tickets Monitoring, Scraping and Purchase System

## System Environment
- **OS**: Ubuntu 24.04 LTS  
- **Web Server**: Apache2
- **PHP Version**: 8.4
- **Database**: MySQL/MariaDB 10.4

## Backed Up Files
1. `composer.json` - PHP dependencies configuration
2. `composer.lock` - PHP dependencies lock file  
3. `package.json` - Node.js dependencies configuration
4. `package-lock.json` - Node.js dependencies lock file

## Current PHP Dependencies Summary (composer.json)

### Production Dependencies
- **PHP**: ^8.4
- **Laravel Framework**: ^12.0
- **Laravel Sanctum**: ^4.0 (API authentication)
- **Laravel Passport**: ^13.0 (OAuth2 server)
- **Guzzle HTTP**: ^7.0 (HTTP client)
- **Symfony Components**: DOM crawler, CSS selector (^7.0)
- **PDF Generation**: barryvdh/laravel-dompdf ^3.0
- **Activity Logging**: spatie/laravel-activitylog ^4.8
- **Communications**: twilio/sdk ^8.7, pusher/pusher-php-server ^7.2
- **Redis**: predis/predis ^3.1
- **Web Scraping**: roach-php/laravel ^3.0, spatie/browsershot ^5.0.5
- **Security**: pragmarx/google2fa ^8.0 with QR codes
- **Payments**: stripe/stripe-php ^17.4, paypal/paypal-server-sdk ^1.1
- **Queue Management**: laravel/horizon ^5.33
- **Image Processing**: intervention/image ^3.0
- **Data Export**: maatwebsite/excel ^3.1, phpoffice/phpspreadsheet ^1.30
- **User Agent Detection**: jenssegers/agent ^2.6

### Development Dependencies  
- **Testing**: phpunit/phpunit ^12.0, mockery/mockery ^1.6
- **Code Quality**: larastan/larastan ^3.0, phpstan/phpstan ^2.0
- **Code Style**: laravel/pint ^1.0, friendsofphp/php-cs-fixer ^3.64
- **Debugging**: laravel/telescope ^5.10, spatie/laravel-ignition ^2.8
- **Performance Testing**: brianium/paratest ^7.8
- **Code Analysis**: phpmd/phpmd ^2.15, squizlabs/php_codesniffer ^3.10
- **Metrics**: phpmetrics/phpmetrics ^2.9
- **Refactoring**: rector/rector ^2.0

## Current Node.js Dependencies Summary (package.json)

### Production Dependencies
- **Vue.js Ecosystem**: vue ^3.4.15, vue-router ^4.2.5, pinia ^2.1.7
- **Alpine.js**: ^3.14.9 with plugins (collapse, focus, intersect, persist)
- **UI Components**: @headlessui/vue ^1.7.16, @heroicons/vue ^2.0.18
- **Utilities**: @vueuse/core ^10.7.2, @vueuse/components ^10.7.2
- **Data Fetching**: @tanstack/vue-query ^5.17.19
- **HTTP Client**: axios ^1.6.7
- **Charts**: chart.js ^4.4.1 with date adapter
- **Real-time**: socket.io-client ^4.7.4
- **Animations**: @vueuse/motion ^2.0.0, framer-motion ^11.0.5
- **Search**: fuse.js ^7.0.0
- **Drag & Drop**: sortablejs ^1.15.2
- **Virtual Keyboard**: virtual-keyboard ^1.30.4
- **Validation**: zod ^3.22.4
- **Utilities**: lodash-es ^4.17.21, date-fns ^3.3.1, mitt ^3.0.1

### Development Dependencies
- **Build Tools**: vite ^5.4.8, @vitejs/plugin-vue ^5.0.3
- **TypeScript**: typescript ^5.3.3, vue-tsc ^2.1.6
- **Testing**: vitest ^2.1.1, @vue/test-utils ^2.4.4, jsdom ^24.0.0
- **Code Quality**: eslint ^8.56.0, prettier ^3.2.5
- **PWA**: vite-plugin-pwa ^0.17.5, workbox-cli ^7.0.0
- **CSS**: windicss ^3.5.6, autoprefixer ^10.4.17, postcss ^8.4.35

## Current Working Features

### Core Sports Ticket Management
✅ **Multi-Platform Ticket Monitoring**
- Ticketmaster integration and scraping
- StubHub API integration  
- Viagogo platform monitoring
- TickPick platform support
- SeeTickets UK monitoring
- Football club direct store monitoring (Arsenal, Chelsea, Liverpool, Manchester United, etc.)
- Cricket venue monitoring (Lords, Twickenham, etc.)
- F1 venues (Silverstone)
- Tennis venues (Wimbledon)

✅ **Real-Time Monitoring System**
- Event-driven architecture with Laravel Events
- Domain-driven design implementation
- Real-time price tracking and alerts
- Availability status monitoring
- Smart alert escalation system
- Performance monitoring and caching

✅ **Advanced Analytics Dashboard**  
- Interactive charts and metrics visualization
- Real-time performance monitoring
- Price volatility analysis
- High-demand ticket identification
- Advanced filtering and search capabilities

✅ **Security & Authentication**
- Two-factor authentication (2FA) with Google Authenticator
- Role-based access control (Admin, Agent, Customer)
- API security with rate limiting
- Account security monitoring
- Password compromise checking
- Comprehensive audit logging

✅ **Notification System**
- Multi-channel notifications (Email, SMS, Push, Slack, Discord, Telegram)
- Smart alert preferences and thresholds  
- In-app notification system
- Webhook notification support
- Alert escalation workflows

✅ **User Management**
- User preference management
- Favorite teams and venues tracking
- Price preference settings
- Comprehensive user activity tracking
- Account deletion protection
- Data export capabilities

✅ **Automated Purchase System**
- Purchase decision automation
- Multi-platform order management
- Payment processing (Stripe, PayPal)
- Purchase workflow orchestration

### Technical Infrastructure
✅ **Modern Frontend Stack**
- Vue.js 3 with Composition API
- Alpine.js for interactive components
- Tailwind CSS with Windi CSS
- Progressive Web App (PWA) capabilities
- Real-time updates with Socket.io
- Responsive design for all devices

✅ **Backend Architecture**
- Laravel 12 with PHP 8.4
- Domain-driven design patterns
- Event sourcing and CQRS implementation
- Advanced caching strategies (Redis)
- Queue processing with Horizon
- Database optimization and indexing

✅ **Development Workflow**
- Comprehensive testing suite (PHPUnit, Vitest)
- Code quality tools (PHPStan, ESLint, Prettier)
- Automated CI/CD pipelines
- Performance monitoring and profiling
- Database migration and seeding strategies

## Notes
- This backup was created during Step 2 of the upgrade process
- All files are in stable working condition
- System is currently on main branch with latest commits
- No critical issues detected in current dependency versions
- Ready for Phase 4 upgrade implementation

## Restoration Instructions
To restore these dependencies:

```bash
# Restore PHP dependencies
cp composer.json composer.lock ./
composer install

# Restore Node.js dependencies  
cp package.json package-lock.json ./
npm install

# Run application setup
php artisan config:clear
php artisan cache:clear
npm run build
```
