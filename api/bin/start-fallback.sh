#!/usr/bin/env sh
set -e

# Attendre que la base de données soit disponible
echo "Waiting for database connection..."
while ! php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
    echo "Database not ready, waiting..."
    sleep 2
done

echo "Database is ready, cleaning up existing tables..."
# Supprimer directement les tables problématiques
php bin/console doctrine:query:sql "DROP TABLE IF EXISTS notification CASCADE" || true
php bin/console doctrine:query:sql "DROP TABLE IF EXISTS achievement CASCADE" || true
php bin/console doctrine:query:sql "DROP TABLE IF EXISTS chat_message CASCADE" || true
php bin/console doctrine:query:sql "DROP TABLE IF EXISTS exercise CASCADE" || true
php bin/console doctrine:query:sql "DROP TABLE IF EXISTS game_session CASCADE" || true
php bin/console doctrine:query:sql "DROP TABLE IF EXISTS leaderboard CASCADE" || true
php bin/console doctrine:query:sql "DROP TABLE IF EXISTS lesson CASCADE" || true
php bin/console doctrine:query:sql "DROP TABLE IF EXISTS progression CASCADE" || true
php bin/console doctrine:query:sql "DROP TABLE IF EXISTS \"user\" CASCADE" || true

# Vider la table des migrations
php bin/console doctrine:query:sql "DELETE FROM doctrine_migration_versions" || true

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "Starting Apache..."
exec apache2-foreground
