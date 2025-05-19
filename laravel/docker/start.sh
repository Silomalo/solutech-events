#!/bin/bash

# Wait for database to be ready
echo "Waiting for database connection..."
while ! pg_isready -h db -U $DB_USERNAME -d $DB_DATABASE -t 1; do
    echo "Waiting for PostgreSQL to start..."
    sleep 2
done
echo "PostgreSQL is up and running."

# Apply Laravel key if not set
echo "Setting up Laravel application..."
php artisan key:generate --no-interaction --force
php artisan config:cache
php artisan route:cache

# Run migrations and seeds
echo "Running database migrations..."
php artisan migrate --force
echo "Running database seeding..."
php artisan db:seed --force
echo "Running tenant migrations..."
php artisan tenants:migrate

# Clear caches and optimize
echo "Optimizing application..."
php artisan optimize:clear
php artisan optimize

# Build assets 
echo "Building frontend assets..."
npm run build

# Ensure proper permissions
echo "Setting proper file permissions..."
chown -R laravel:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Start queue worker in background
echo "Starting Laravel queue worker..."
php artisan queue:work --daemon &

# Start supervisor (which will manage other processes)
echo "Starting supervisor service..."
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
