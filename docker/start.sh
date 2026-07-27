#!/bin/bash

# Generer la cle de l'application si elle n'existe pas
php artisan key:generate --force

# Nettoyer et mettre en cache la configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Exectuer les migrations de la base de donnees (forcer en production)
php artisan migrate --force

# Lancer Apache en arriere-plan (Foreground)
apache2-foreground
