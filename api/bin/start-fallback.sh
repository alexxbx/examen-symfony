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

echo "Creating tables directly..."
# Créer les tables directement avec les bonnes structures
php bin/console doctrine:query:sql "CREATE TABLE IF NOT EXISTS \"user\" (id SERIAL PRIMARY KEY, username VARCHAR(20) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, roles JSON NOT NULL)" || true
php bin/console doctrine:query:sql "CREATE UNIQUE INDEX IF NOT EXISTS unique_username ON \"user\" (username)" || true
php bin/console doctrine:query:sql "CREATE UNIQUE INDEX IF NOT EXISTS unique_email ON \"user\" (email)" || true

php bin/console doctrine:query:sql "CREATE TABLE IF NOT EXISTS lesson (id SERIAL PRIMARY KEY, title VARCHAR(255) NOT NULL, content TEXT NOT NULL, level VARCHAR(255) DEFAULT NULL, \"order\" INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)" || true

php bin/console doctrine:query:sql "CREATE TABLE IF NOT EXISTS achievement (id SERIAL PRIMARY KEY, user_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, required_lessons INT NOT NULL, icon VARCHAR(255) NOT NULL, unlocked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)" || true
php bin/console doctrine:query:sql "ALTER TABLE achievement ADD CONSTRAINT IF NOT EXISTS FK_96737FF1A76ED395 FOREIGN KEY (user_id) REFERENCES \"user\" (id)" || true

php bin/console doctrine:query:sql "CREATE TABLE IF NOT EXISTS chat_message (id SERIAL PRIMARY KEY, user_id INT NOT NULL, message TEXT NOT NULL, is_from_user BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)" || true
php bin/console doctrine:query:sql "ALTER TABLE chat_message ADD CONSTRAINT IF NOT EXISTS FK_FAB3FC16A76ED395 FOREIGN KEY (user_id) REFERENCES \"user\" (id)" || true

php bin/console doctrine:query:sql "CREATE TABLE IF NOT EXISTS exercise (id SERIAL PRIMARY KEY, lesson_id INT DEFAULT NULL, question VARCHAR(255) NOT NULL, options JSON NOT NULL, answer VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, cours TEXT DEFAULT NULL)" || true
php bin/console doctrine:query:sql "ALTER TABLE exercise ADD CONSTRAINT IF NOT EXISTS FK_AEDAD51CCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)" || true

php bin/console doctrine:query:sql "CREATE TABLE IF NOT EXISTS game_session (id SERIAL PRIMARY KEY, host_id INT NOT NULL, guest_id INT DEFAULT NULL, winner_id INT DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ended_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, host_score INT DEFAULT NULL, guest_score INT DEFAULT NULL, game_data JSON DEFAULT NULL)" || true
php bin/console doctrine:query:sql "ALTER TABLE game_session ADD CONSTRAINT IF NOT EXISTS FK_4586AAFB1FB8D185 FOREIGN KEY (host_id) REFERENCES \"user\" (id)" || true
php bin/console doctrine:query:sql "ALTER TABLE game_session ADD CONSTRAINT IF NOT EXISTS FK_4586AAFB9A4AA658 FOREIGN KEY (guest_id) REFERENCES \"user\" (id)" || true
php bin/console doctrine:query:sql "ALTER TABLE game_session ADD CONSTRAINT IF NOT EXISTS FK_4586AAFB5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES \"user\" (id)" || true

php bin/console doctrine:query:sql "CREATE TABLE IF NOT EXISTS leaderboard (id SERIAL PRIMARY KEY, user_id INT NOT NULL, total_games INT NOT NULL, wins INT NOT NULL, losses INT NOT NULL, draws INT NOT NULL, total_points INT NOT NULL, last_updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)" || true
php bin/console doctrine:query:sql "ALTER TABLE leaderboard ADD CONSTRAINT IF NOT EXISTS FK_182E5253A76ED395 FOREIGN KEY (user_id) REFERENCES \"user\" (id)" || true

php bin/console doctrine:query:sql "CREATE TABLE IF NOT EXISTS progression (id SERIAL PRIMARY KEY, user_id INT NOT NULL, lesson_id INT NOT NULL, completed BOOLEAN NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, unlocked BOOLEAN NOT NULL)" || true
php bin/console doctrine:query:sql "ALTER TABLE progression ADD CONSTRAINT IF NOT EXISTS FK_D5B25073A76ED395 FOREIGN KEY (user_id) REFERENCES \"user\" (id)" || true
php bin/console doctrine:query:sql "ALTER TABLE progression ADD CONSTRAINT IF NOT EXISTS FK_D5B25073CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)" || true

echo "Tables created, marking migrations as executed..."
# Marquer toutes les migrations comme exécutées
php bin/console doctrine:migrations:version --add --all --version=DoctrineMigrations\Version20250509112746 || true
php bin/console doctrine:migrations:version --add --all --version=DoctrineMigrations\Version20250513083700 || true
php bin/console doctrine:migrations:version --add --all --version=DoctrineMigrations\Version20250514084144 || true
php bin/console doctrine:migrations:version --add --all --version=DoctrineMigrations\Version20250522120044 || true
php bin/console doctrine:migrations:version --add --all --version=DoctrineMigrations\Version20250522120237 || true
php bin/console doctrine:migrations:version --add --all --version=DoctrineMigrations\Version20250522120845 || true
php bin/console doctrine:migrations:version --add --all --version=DoctrineMigrations\Version20250522123203 || true
php bin/console doctrine:migrations:version --add --all --version=DoctrineMigrations\Version20250812112454 || true
php bin/console doctrine:migrations:version --add --all --version=DoctrineMigrations\Version20250812121500 || true
php bin/console doctrine:migrations:version --add --all --version=DoctrineMigrations\Version20250812121600 || true

echo "Starting Apache..."
exec apache2-foreground
