#!/bin/bash
# HomelabTV Cache Clear Script for Docker
# This script clears all Laravel caches inside the Docker container

echo "🧹 Clearing HomelabTV caches (Docker)..."
echo ""

# Clear view cache (fixes Livewire and other template issues)
echo "Clearing view cache..."
docker-compose exec app php artisan view:clear

# Clear application cache
echo "Clearing application cache..."
docker-compose exec app php artisan cache:clear

# Clear config cache
echo "Clearing config cache..."
docker-compose exec app php artisan config:clear

# Clear route cache
echo "Clearing route cache..."
docker-compose exec app php artisan route:clear

# Clear compiled files
echo "Clearing compiled files..."
docker-compose exec app php artisan clear-compiled

echo ""
echo "✅ All caches cleared successfully!"
echo ""
echo "If you're still experiencing issues, try:"
echo "  - Restarting the container: docker-compose restart app"
echo "  - Running: docker-compose exec app php artisan optimize"
echo "  - Checking logs: docker-compose logs app"
