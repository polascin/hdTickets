#!/bin/bash

# HD Tickets - Fix Laravel Permissions Script
# This script fixes common Laravel permission issues for the HD Tickets project

echo "🔧 HD Tickets - Fixing Laravel Permissions..."

PROJECT_PATH="/var/www/hdtickets"
WEB_SERVER_USER="www-data"
WEB_SERVER_GROUP="www-data"

# Check if project directory exists
if [ ! -d "$PROJECT_PATH" ]; then
    echo "❌ Error: Project directory $PROJECT_PATH does not exist!"
    exit 1
fi

echo "📁 Project Path: $PROJECT_PATH"
echo "👤 Web Server User: $WEB_SERVER_USER"
echo "👥 Web Server Group: $WEB_SERVER_GROUP"

# Fix ownership for critical directories
echo "🔐 Setting ownership for critical directories..."

# Storage directory - Laravel's writable storage
sudo chown -R $WEB_SERVER_USER:$WEB_SERVER_GROUP "$PROJECT_PATH/storage/"
echo "✅ Storage directory ownership fixed"

# Bootstrap cache - Laravel's optimization cache
sudo chown -R $WEB_SERVER_USER:$WEB_SERVER_GROUP "$PROJECT_PATH/bootstrap/cache/"
echo "✅ Bootstrap cache ownership fixed"

# Fix permissions for critical directories
echo "📝 Setting permissions for critical directories..."

# Storage directory permissions (775 allows group write)
sudo chmod -R 775 "$PROJECT_PATH/storage/"
echo "✅ Storage directory permissions set to 775"

# Bootstrap cache permissions
sudo chmod -R 775 "$PROJECT_PATH/bootstrap/cache/"
echo "✅ Bootstrap cache permissions set to 775"

# Clear Laravel caches to force regeneration with correct permissions
echo "🧹 Clearing Laravel caches..."

cd "$PROJECT_PATH"

# Clear view cache
php artisan view:clear && echo "✅ View cache cleared"

# Clear config cache  
php artisan config:clear && echo "✅ Config cache cleared"

# Clear route cache
php artisan route:clear && echo "✅ Route cache cleared"

# Clear application cache
php artisan cache:clear && echo "✅ Application cache cleared"

echo ""
echo "🎉 Laravel permissions have been fixed successfully!"
echo ""
echo "📊 Directory Status:"
echo "   Storage: $(ls -ld $PROJECT_PATH/storage/ | awk '{print $1, $3, $4}')"
echo "   Bootstrap Cache: $(ls -ld $PROJECT_PATH/bootstrap/cache/ | awk '{print $1, $3, $4}')"
echo ""
echo "🌐 You can now access HD Tickets at: https://hdtickets.local/"
echo ""
echo "💡 Tip: Run this script whenever you encounter permission issues"
echo "   after editing files outside of the web server context."
