#!/bin/bash
set -e

echo "======================================="
echo " DEMARRAGE - Universal Invest Strategy"
echo "======================================="

# Lien symbolique pour le stockage public
php artisan storage:link || true

# Vider tout le cache (important en production)
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generer la cle si absente
php artisan key:generate --force

# Mettre en cache pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrer la base de donnees
echo "Migration de la base de donnees..."
php artisan migrate --force --no-interaction

echo "Application prete ! Demarrage Apache..."
apache2-foreground
