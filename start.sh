#!/bin/sh

# Mark the Laravel folder as safe for Git
git config --global --add safe.directory /var/www

# Start PHP-FPM in the background
php-fpm -D

# Ensure storage and bootstrap/cache directories have correct permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Automatically install composer dependencies if missing
if [ ! -d "vendor" ]; then
  echo "Running composer install..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Run Laravel setup steps
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan key:generate --force

# Finally start Apache
httpd -D FOREGROUND
