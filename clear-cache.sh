#!/bin/bash
# HomelabTV Cache Clear Script
# This script clears all Laravel caches to resolve common issues

echo "🧹 Clearing HomelabTV caches..."
echo ""

# Clear view cache (fixes Livewire and other template issues)
echo "Clearing view cache..."
php artisan view:clear

# Clear application cache
echo "Clearing application cache..."
php artisan cache:clear

# Clear config cache
echo "Clearing config cache..."
php artisan config:clear

# Clear route cache
echo "Clearing route cache..."
php artisan route:clear

# Clear compiled files
echo "Clearing compiled files..."
php artisan clear-compiled

echo ""
echo "✅ All caches cleared successfully!"
echo ""
echo "If you're still experiencing issues, try:"
echo "  - Restarting your web server"
echo "  - Running: php artisan optimize"
echo "  - Checking storage/logs/laravel.log for errors"

exit 0
