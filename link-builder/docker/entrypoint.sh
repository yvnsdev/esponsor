#!/bin/sh
set -e

# Create log directory for PHP
mkdir -p /var/log/php
chown www-data:www-data /var/log/php

# Ensure storage directories exist with proper permissions
mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink if not exists
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link 2>/dev/null || true
fi

# Generate application key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    php artisan key:generate --force
fi

# Cache configuration for production
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Run migrations
php artisan migrate --force 2>/dev/null || echo "Migration failed or already up to date"

echo "============================================"
echo "  Link Builder is ready!"
echo "============================================"

exec "$@"
