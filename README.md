# HD Tickets - Sports Event Monitoring Platform

Professional sports event ticket monitoring and discovery system built with Laravel 12 and modern web technologies.

## 🚀 Features

- **Enterprise-Grade Backend**: Laravel 12 framework with PHP 8.4
- **Real-time Monitoring**: Live ticket price tracking with WebSocket support via Laravel Echo & Pusher
- **Advanced Authentication**: Multi-factor authentication, session management, and RBAC
- **Sports-Focused Design**: Enterprise-grade UI with sports-themed color schemes and components
- **Web Scraping Integration**: Advanced ticket platform monitoring with Roach PHP
- **Mobile-First Design**: Responsive design optimized for all devices with PWA support
- **Advanced Security**: Enhanced login security, rate limiting, and fraud detection

## 🛠 Tech Stack

### Backend
- **Laravel 12** - Latest Laravel framework with modern PHP features
- **PHP 8.4** - Latest PHP with performance and type safety improvements
- **Laravel Passport** - OAuth2 server for API authentication
- **Laravel Sanctum** - SPA authentication for frontend integration
- **Laravel Horizon** - Queue management and monitoring

### Frontend
- **Alpine.js** - Lightweight reactive framework for interactive components
- **Tailwind CSS 3.4** - Utility-first CSS with sports-themed design system
- **Vite** - Modern build tool for fast development and optimized production builds
- **TypeScript** - Type safety for JavaScript components

### Real-time & Communication
- **Laravel Echo** - WebSocket integration for real-time features
- **Pusher** - WebSocket service for live updates
- **Redis** - High-performance caching and session storage

### Development & Quality
- **ESLint** - JavaScript/TypeScript code linting
- **Prettier** - Code formatting with Tailwind plugin
- **Laravel Pint** - PHP code formatting
- **PHPStan/Larastan** - Static analysis for PHP code
- **PHPUnit** - Comprehensive testing framework

### Scraping & Monitoring
- **Roach PHP** - Professional web scraping framework
- **Symfony DOMCrawler** - HTML/XML document parsing
- **Guzzle HTTP** - HTTP client for API integrations
- **Browsershot/Puppeteer** - Browser automation for complex scraping

### Database & Storage
- **MySQL/PostgreSQL** - Primary database storage
- **Redis** - Session storage and caching
- **Laravel Eloquent ORM** - Database abstraction and relationships

### Payment & Integrations
- **Stripe** - Payment processing integration
- **PayPal Server SDK** - PayPal payment integration
- **Twilio** - SMS notifications and alerts
- **Slack Notifications** - Team communication integration

## 📁 Project Structure

```
app/
├── Domain/                 # Domain-Driven Design architecture
│   ├── Ticket/            # Ticket domain logic
│   ├── User/              # User management domain
│   └── Payment/           # Payment processing domain
├── Http/                   # HTTP layer (Controllers, Middleware, Requests)
│   ├── Controllers/       
│   │   ├── Auth/          # Authentication controllers
│   │   ├── Api/           # API endpoints
│   │   └── Dashboard/     # Dashboard controllers
│   ├── Middleware/        # Custom middleware
│   └── Requests/          # Form request validation
├── Services/              # Application services
│   ├── Security/          # Security-related services
│   ├── Scraping/          # Web scraping services
│   └── Payment/           # Payment processing services
├── Models/                # Eloquent models
├── Jobs/                  # Background job classes
└── Events/                # Event classes

resources/
├── js/                    # Frontend JavaScript/TypeScript
│   ├── components/        # Alpine.js components
│   ├── services/          # Frontend services
│   └── app.js             # Main application entry
├── css/                   # Stylesheets
├── views/                 # Blade templates
└── public/                # Static assets

config/                    # Laravel configuration files
routes/                    # Application routes
database/                  # Migrations, seeders, factories
tests/                     # PHPUnit tests
```

## 🏃‍♂️ Getting Started

### Prerequisites
- **PHP 8.4+** with required extensions (mbstring, xml, gd, curl, zip, etc.)
- **Composer 2.0+** for PHP dependency management
- **Node.js 18+** and **npm 9+** for frontend assets
- **MySQL 8.0+** or **PostgreSQL 13+** database
- **Redis 6.0+** for caching and sessions
- **Web server** (Apache 2.4+ or Nginx 1.18+)

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/polascin/hdTickets.git
   cd hdTickets
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**:
   ```bash
   npm install
   ```

4. **Environment setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build frontend assets**:
   ```bash
   npm run build
   ```

7. **Start the application**:
   ```bash
   php artisan serve
   ```
   The app will be available at `http://localhost:8000`

### Development Workflow

1. **Start development servers**:
   ```bash
   # Backend (Laravel)
   php artisan serve
   
   # Frontend assets (Vite with hot reload)
   npm run dev
   
   # Queue worker (for background jobs)
   php artisan horizon
   ```

2. **Code quality checks**:
   ```bash
   # PHP code formatting
   vendor/bin/pint
   
   # PHP static analysis
   vendor/bin/phpstan analyse
   
   # Frontend linting
   npm run lint
   ```

## 🎯 Code Quality & Standards

The project maintains high code quality through automated tools and strict standards:

### PHP Quality Tools
- **Laravel Pint**: PSR-12 compliant code formatting with 381 style issues resolved
- **PHPStan Level 10**: Static analysis with zero errors at maximum strictness
- **PHPUnit**: Comprehensive test coverage for backend functionality
- **Rector**: Automated code upgrades and refactoring support

### Frontend Quality Tools  
- **ESLint**: TypeScript-aware linting with 86 remaining unused variable checks
- **Prettier**: Consistent code formatting across JavaScript/TypeScript files
- **TypeScript**: Type safety for complex frontend interactions
- **Vite**: Modern build tool with optimized production bundles (1.97s build time)

### Performance Optimizations
- **Laravel Optimization**: Config, routes, events, and views fully cached
- **Redis Integration**: Session storage, caching, and queue management
- **Asset Optimization**: Vite-powered builds with tree shaking and code splitting
- **Database Performance**: Query optimization with N+1 detection and eager loading

### Development Experience
- **Development Server**: Fast startup (128ms) with hot module replacement  
- **Build Performance**: Production builds complete in under 2 seconds
- **Linting Integration**: Pre-commit hooks for code quality enforcement
- **Automated Optimization**: Built-in performance optimization commands

### Available Scripts

- `composer install` - Install PHP dependencies
- `npm run dev` - Start Vite development server
- `npm run build` - Build production assets  
- `npm run lint` - Run ESLint on JavaScript/TypeScript
- `php artisan serve` - Start Laravel development server
- `php artisan test` - Run PHPUnit tests
- `php artisan horizon` - Start queue management dashboard
- `php artisan optimize` - Cache config, routes, events, and views
- `vendor/bin/pint` - Format PHP code according to PSR-12
- `vendor/bin/phpstan analyse` - Run static analysis on PHP code

## 🎨 Design System

The platform features a comprehensive design system with:

- **Sports League Colors**: NFL, NBA, MLB, NHL, MLS themed color palettes
- **Enterprise Components**: Professional UI components built with Alpine.js and Tailwind CSS
- **Responsive Grid**: Mobile-first layout system with modern CSS Grid and Flexbox
- **Accessibility**: WCAG compliant components with proper ARIA support
- **Dark Mode**: Full dark theme support with automatic system preference detection

## 🔄 Real-time Features

- **Live Price Tracking**: Monitor ticket prices across multiple platforms in real-time
- **WebSocket Integration**: Laravel Echo + Pusher for instant updates without page refresh
- **Push Notifications**: Browser notifications for price changes and alerts
- **Background Processing**: Laravel Horizon for queue management and background jobs
- **Redis Caching**: High-performance caching for frequently accessed data

## 📱 Mobile & PWA Experience

- **Progressive Web App**: Installable PWA with service worker support
- **Touch-Optimized**: Gestures and interactions designed for mobile devices
- **Offline Support**: Core functionality available without internet connection
- **Performance Optimized**: Fast loading with Vite optimization and Laravel caching
- **Responsive Design**: Fluid layouts that work on all screen sizes

## 🔐 Security Features

- **Multi-Factor Authentication**: Google Authenticator integration with QR codes
- **Enhanced Login Security**: Device fingerprinting, geolocation checks, and automated tool detection
- **Role-Based Access Control (RBAC)**: Granular permissions for Admin, Agent, Customer, and Scraper roles
- **Session Management**: Secure session handling with Redis storage
- **Rate Limiting**: Advanced rate limiting to prevent abuse and attacks
- **CSRF Protection**: Laravel's built-in CSRF token validation
- **Input Validation**: Comprehensive server-side validation with Laravel Form Requests
- **API Security**: Laravel Passport OAuth2 server with token management

## 🚀 Deployment

The application supports various deployment strategies:

### Production Deployment
- **Apache/Nginx**: Traditional web server deployment with SSL/TLS
- **Docker**: Containerized deployment with Docker Compose
- **Cloud Platforms**: AWS, Google Cloud, DigitalOcean optimized configurations
- **Load Balancing**: Multiple server deployment with Redis session sharing

### Environment Configuration
```bash
# Production environment setup
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hdtickets_prod

# Redis configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue configuration
QUEUE_CONNECTION=redis
HORIZON_BALANCE=auto
```

## 🔧 Development & Testing

### Code Quality Standards
- **Laravel Pint**: PHP code formatting following PSR-12 standards
- **PHPStan/Larastan**: Static analysis at level 8 for type safety
- **ESLint + Prettier**: JavaScript/TypeScript code quality
- **Git Hooks**: Pre-commit validation with code quality checks

### Testing Framework
- **PHPUnit**: Comprehensive backend testing with Feature and Unit tests
- **Database Testing**: In-memory SQLite for fast test execution
- **API Testing**: Complete API endpoint testing with authentication
- **Browser Testing**: Laravel Dusk for end-to-end testing

### Performance Monitoring
- **Laravel Telescope**: Development debugging and profiling
- **Laravel Horizon**: Queue monitoring and management dashboard
- **Database Query Optimization**: N+1 query detection and prevention
- **Redis Monitoring**: Cache hit rates and performance metrics

## 📊 System Architecture

### Domain-Driven Design (DDD)
The application follows DDD principles with clear domain boundaries:

- **Ticket Domain**: Core business logic for ticket monitoring and management
- **User Domain**: Authentication, authorization, and user management  
- **Payment Domain**: Payment processing and financial transactions
- **Scraping Domain**: Web scraping and data collection services

### Event-Driven Architecture
- **Laravel Events**: Domain events for loosely coupled components
- **Queue Jobs**: Asynchronous processing for heavy operations
- **WebSocket Events**: Real-time updates through Laravel Echo
- **Database Events**: Eloquent model events for audit logging

### API Design
- **RESTful APIs**: Resource-based API endpoints following REST principles
- **API Versioning**: Version management for backward compatibility
- **Rate Limiting**: Per-user and per-endpoint rate limiting
- **Documentation**: Comprehensive API documentation with examples

## 🤝 Contributing

We welcome contributions to improve the HD Tickets platform:

1. **Fork the repository** and create a feature branch
2. **Follow code standards**: Use Laravel Pint and ESLint configurations
3. **Write tests**: Include PHPUnit tests for backend changes
4. **Update documentation**: Keep README and API docs current
5. **Submit a pull request** with detailed description of changes

### Development Guidelines
- Follow PSR-12 coding standards for PHP
- Use TypeScript for all new JavaScript code
- Write comprehensive test coverage for new features
- Follow semantic commit message conventions
- Ensure all local checks pass before merging (e.g., make full-check)

## 📄 License

This project is proprietary software for HD Tickets platform.

**Copyright © 2025 HD Tickets**  
All rights reserved. Unauthorized reproduction or distribution is prohibited.

---

## 🔄 System Information

### Current Version: 5.0.0

### Technology Stack Summary
- **Backend**: Laravel 12.22.1 + PHP 8.4.11
- **Frontend**: Alpine.js 3.14 + Tailwind CSS 3.4.17 + Vite 6.0.7
- **Database**: MySQL 8.0+ with Laravel Eloquent ORM
- **Caching**: Redis 6.0+ for sessions, cache, and queues
- **Real-time**: Laravel Echo + Pusher for WebSocket communication
- **Authentication**: Laravel Passport (OAuth2) + Sanctum (SPA)
- **Queue Management**: Laravel Horizon with Redis driver
- **Web Scraping**: Roach PHP 3.0 + Symfony DOMCrawler 7.0

### Key Features Implemented
- ✅ Multi-factor Authentication (2FA) with Google Authenticator
- ✅ Enhanced Login Security with device fingerprinting
- ✅ Role-Based Access Control (Admin, Agent, Customer, Scraper)
- ✅ Real-time ticket price monitoring with WebSocket updates
- ✅ Advanced web scraping with Roach PHP framework
- ✅ Payment processing with Stripe and PayPal integration
- ✅ Progressive Web App (PWA) with offline capabilities
- ✅ Comprehensive audit logging with Spatie Activity Log
- ✅ Professional dashboard with sports-themed design
- ✅ Mobile-responsive design with dark mode support

### Performance Metrics
- **PHP Version**: 8.4.11 (Latest stable)
- **Laravel Version**: 12.22.1 (Latest LTS)
- **Code Quality**: PHPStan Level 8 compliant
- **Test Coverage**: Comprehensive PHPUnit test suite
- **Security Score**: Enhanced with multi-layer protection
- **Mobile Performance**: PWA optimized with service workers
