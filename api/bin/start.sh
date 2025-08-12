#!/usr/bin/env sh
set -e

# Attendre que la base de données soit disponible
echo "Waiting for database connection..."
while ! php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
    echo "Database not ready, waiting..."
    sleep 2
done

echo "Database is ready, running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "Starting Apache..."
exec apache2-foreground