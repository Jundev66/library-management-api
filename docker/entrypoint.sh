#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

echo "Checking database connection..."
max_retries=30
count=0
until php artisan db:monitor > /dev/null 2>&1 || [ $count -eq $max_retries ]; do
    sleep 1
    count=$((count + 1))
done

echo "Running defensive migrations and idempotent seeders..."
php artisan migrate --force
php artisan db:seed --force

echo "Starting application server..."
if [ $# -gt 0 ]; then
    exec "$@"
else
    exec php artisan serve --host=0.0.0.0 --port=8000
fi
