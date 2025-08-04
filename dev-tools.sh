#!/bin/bash

# HD Tickets Development Tools Script
# Collection of useful development commands

show_help() {
    echo "HD Tickets Development Tools"
    echo "Usage: ./dev-tools.sh [command]"
    echo ""
    echo "Available commands:"
    echo "  start       - Start development environment"
    echo "  stop        - Stop all background processes"
    echo "  test        - Run all tests"
    echo "  migrate     - Run database migrations"
    echo "  seed        - Seed the database with test data"
    echo "  fresh       - Fresh database migration with seeding"
    echo "  cache       - Clear all caches"
    echo "  assets      - Build frontend assets"
    echo "  watch       - Watch and rebuild assets on changes"
    echo "  logs        - Show application logs"
    echo "  queue       - Monitor queue jobs"
    echo "  routes      - List all routes"
    echo "  scrape      - Test ticket scraping"
    echo "  health      - Check system health"
    echo "  help        - Show this help message"
}

case "$1" in
    "start")
        ./start-dev.sh
        ;;
    "stop")
        echo "🛑 Stopping all background processes..."
        pkill -f "queue:work"
        pkill -f "schedule:work"
        pkill -f "npm run dev"
        echo "✅ All processes stopped"
        ;;
    "test")
        echo "🧪 Running tests..."
        php artisan test
        ;;
    "migrate")
        echo "🗄️  Running migrations..."
        php artisan migrate
        ;;
    "seed")
        echo "🌱 Seeding database..."
        php artisan db:seed
        ;;
    "fresh")
        echo "🆕 Fresh database setup..."
        php artisan migrate:fresh --seed
        ;;
    "cache")
        echo "🧹 Clearing all caches..."
        php artisan cache:clear
        php artisan config:clear
        php artisan route:clear
        php artisan view:clear
        php artisan clear-compiled
        echo "✅ All caches cleared"
        ;;
    "assets")
        echo "🎨 Building assets..."
        npm run build
        ;;
    "watch")
        echo "👀 Watching assets for changes..."
        npm run dev
        ;;
    "logs")
        echo "📝 Application logs:"
        tail -f storage/logs/laravel.log
        ;;
    "queue")
        echo "🔄 Queue status:"
        php artisan queue:monitor
        ;;
    "routes")
        echo "🛣️  Application routes:"
        php artisan route:list
        ;;
    "scrape")
        echo "🕷️  Testing ticket scraping..."
        php artisan scrape:tickets --test
        ;;
    "health")
        echo "🏥 System Health Check"
        echo "====================="
        
        # Check PHP version
        echo "PHP Version: $(php -v | head -n 1)"
        
        # Check Apache status
        if systemctl is-active --quiet apache2; then
            echo "✅ Apache2: Running"
        else
            echo "❌ Apache2: Not running"
        fi
        
        # Check MySQL connection
        if php artisan migrate:status > /dev/null 2>&1; then
            echo "✅ MySQL: Connected"
        else
            echo "❌ MySQL: Connection failed"
        fi
        
        # Check Redis connection
        if redis-cli ping > /dev/null 2>&1; then
            echo "✅ Redis: Connected"
        else
            echo "❌ Redis: Connection failed"
        fi
        
        # Check disk space
        echo "💾 Disk Space:"
        df -h /var/www/hdtickets | tail -n 1
        
        # Check memory usage
        echo "🧠 Memory Usage:"
        free -h | grep Mem
        
        # Check Laravel configuration
        echo "⚙️  Laravel Environment: $(php artisan env)"
        ;;
    "help"|"")
        show_help
        ;;
    *)
        echo "Unknown command: $1"
        show_help
        exit 1
        ;;
esac
