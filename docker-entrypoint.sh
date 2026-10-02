#!/bin/sh
set -e

echo "Running migrations..."
php artisan migrate --force

echo "Starting application..."
exec "$@"
