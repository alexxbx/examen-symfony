#!/usr/bin/env sh
set -e

php bin/console doctrine:migrations:migrate --no-interaction

exec apache2-foreground