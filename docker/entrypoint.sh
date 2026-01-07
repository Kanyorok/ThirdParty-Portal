#!/bin/sh

# Cache configuration at runtime to pick up current environment variables
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Wait for database to be ready (optional but recommended)
echo "Running migrations..."
php artisan migrate --force

echo "Running seeders..."
php artisan db:seed --class=ModuleSeeder --force
php artisan db:seed --class=CodeDetailSeeder --force
php artisan db:seed --class=RolePermissionSeeder --force

# Execute the main container command (e.g., php-fpm or apache)
exec "$@"
