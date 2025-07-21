#!/bin/bash

set -e

# Affiche les fichiers pour debug
echo "Listing contents:"
ls -al

# Installation des dépendances PHP
composer install --no-dev --optimize-autoloader
