# HD Tickets Development Environment

This document provides instructions for setting up and working with the HD Tickets development environment.

## Quick Start

### 1. Start Development Environment
```bash
./start-dev.sh
```

This script will:
- Clear all Laravel caches
- Check database and Redis connections
- Install/update PHP and Node.js dependencies
- Start queue workers and scheduler
- Build frontend assets
- Set up development services

### 2. Development Tools
```bash
./dev-tools.sh [command]
```

Available commands:
- `start` - Start development environment
- `stop` - Stop all background processes
- `test` - Run all tests
- `migrate` - Run database migrations
- `seed` - Seed database with test data
- `fresh` - Fresh database migration with seeding
- `cache` - Clear all caches
- `assets` - Build frontend assets
- `watch` - Watch and rebuild assets on changes
- `logs` - Show application logs
- `queue` - Monitor queue jobs
- `routes` - List all routes
- `scrape` - Test ticket scraping
- `health` - Check system health
- `help` - Show help message

## System Requirements

- **OS**: Ubuntu 24.04 LTS
- **Web Server**: Apache2
- **PHP**: 8.4+
- **Database**: MySQL
- **Cache**: Redis
- **Node.js**: Latest LTS

## Application Architecture

The HD Tickets system is a **Comprehensive Sport Events Entry Tickets Monitoring, Scraping and Purchase System** that includes:

### Core Features
- Multi-platform ticket monitoring
- Real-time scraping of sports events tickets
- Price tracking and analytics
- Alert system for ticket availability
- Purchase automation
- Admin dashboard with analytics

### Key Components
- **Backend**: Laravel 11 framework
- **Frontend**: Vue.js 3 with Vite
- **Styling**: Tailwind CSS
- **Charts**: Chart.js
- **Real-time**: Pusher WebSockets
- **Queue**: Redis-backed job processing
- **Cache**: Redis caching layer

## Development Configuration

### Environment Variables
The application uses a development-specific `.env` configuration with:
- `APP_DEBUG=true` - Full error reporting
- `TELESCOPE_ENABLED=true` - Laravel Telescope debugging
- `DEVELOPMENT_MODE=true` - Development features enabled
- CSS timestamp cache busting enabled

### Important Files
- `start-dev.sh` - Development environment startup script
- `dev-tools.sh` - Development utility commands
- `.env` - Environment configuration
- `DEV_README.md` - This file

## Working with the System

### Accessing the Application
- **Main Application**: http://localhost
- **Admin Panel**: http://localhost/admin
- **API**: http://localhost/api/v1/

### Database Operations
```bash
# Run migrations
./dev-tools.sh migrate

# Seed database with test data
./dev-tools.sh seed

# Fresh migration with seeding
./dev-tools.sh fresh
```

### Asset Development
```bash
# Build assets once
./dev-tools.sh assets

# Watch for changes and rebuild
./dev-tools.sh watch
```

### Testing
```bash
# Run all tests
./dev-tools.sh test

# Test specific functionality
php artisan test --filter=TicketScrapingTest
```

### Queue Management
```bash
# Monitor queue status
./dev-tools.sh queue

# Restart queue workers
php artisan queue:restart
```

### Ticket Scraping
```bash
# Test ticket scraping functionality
./dev-tools.sh scrape

# Run specific scraping tests
php artisan scrape:tickets --platform=ticketmaster --test
```

## Monitoring and Debugging

### Logs
```bash
# View application logs
./dev-tools.sh logs

# Specific log files
tail -f storage/logs/laravel.log
tail -f storage/logs/queue.log
tail -f storage/logs/scheduler.log
```

### System Health
```bash
# Check overall system health
./dev-tools.sh health
```

### Laravel Telescope
When `TELESCOPE_ENABLED=true`, access debugging tools at: http://localhost/telescope

## Important Rules and Guidelines

1. **Ticket Context**: This system deals with **sports event entry tickets**, NOT helpdesk tickets
2. **CSS Caching**: CSS styles are linked with timestamps to prevent caching issues
3. **Development Environment**: Running on Ubuntu 24.04 LTS with Apache2
4. **Code Quality**: Follow PSR standards and existing code patterns

## Troubleshooting

### Common Issues

#### Permission Issues
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### Cache Issues
```bash
./dev-tools.sh cache
```

#### Asset Build Issues
```bash
npm install
npm run build
```

#### Database Connection Issues
- Check MySQL service status
- Verify database credentials in `.env`
- Ensure database exists

#### Redis Connection Issues
```bash
sudo systemctl status redis-server
sudo systemctl start redis-server
```

## Support

For development support or questions about the HD Tickets system:
1. Check the logs for error details
2. Run system health check
3. Verify all services are running
4. Review the relevant documentation in `/docs`

## Next Steps

After setting up the development environment:
1. Review the API documentation in `/docs/API-DOCUMENTATION.md`
2. Understand the scraping system in `/docs/SCRAPING_GUIDE.md`
3. Explore the admin interface at http://localhost/admin
4. Run tests to ensure everything works correctly

Happy coding! 🎫
