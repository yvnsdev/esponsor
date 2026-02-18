#!/bin/sh
set -e

cd /var/www/html

echo "🚀 Starting Link Builder container..."

# --------------------------------------------------
# 1) Create .env automatically if missing
# --------------------------------------------------
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
    echo "[entrypoint] .env creado desde .env.example"
  else
    echo "[entrypoint] ERROR: no existe .env ni .env.example"
    exit 1
  fi
fi

# --------------------------------------------------
# 2) Generate APP_KEY if missing
# --------------------------------------------------
if ! grep -q "^APP_KEY=base64:" .env; then
  php artisan key:generate --force
  echo "[entrypoint] APP_KEY generado"
fi

# --------------------------------------------------
# 3) Create PHP log directory
# --------------------------------------------------
mkdir -p /var/log/php
chown www-data:www-data /var/log/php

# --------------------------------------------------
# 4) Ensure Laravel storage permissions
# --------------------------------------------------
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs
mkdir -p bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# --------------------------------------------------
# 5) Create storage symlink
# --------------------------------------------------
if [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

# --------------------------------------------------
# 6) Wait for DB (important for first boot)
# --------------------------------------------------
echo "[entrypoint] Waiting for database..."
until php -r "
try {
    new PDO('mysql:host=db;port=3306;dbname=linkbuilder', 'linkbuilder', 'secret');
    echo 'DB ready';
} catch (Exception \$e) {
    exit(1);
}"
do
  sleep 2
done
echo " ✔ Database ready"

# --------------------------------------------------
# 7) Run migrations safely
# --------------------------------------------------
php artisan migrate --force || echo "Migrations skipped"

# --------------------------------------------------
# 8) Cache config only in production
# --------------------------------------------------
APP_ENV_VALUE=$(grep "^APP_ENV=" .env | cut -d '=' -f2)

if [ "$APP_ENV_VALUE" = "production" ]; then
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  echo "[entrypoint] Laravel cached for production"
fi

echo "============================================"
echo "   Link Builder is READY 🚀"
echo "============================================"

exec "$@"
