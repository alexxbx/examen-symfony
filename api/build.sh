#!/bin/bash
set -e

echo "🔧 Install des dépendances PHP via Composer"
composer install --no-dev --optimize-autoloader
