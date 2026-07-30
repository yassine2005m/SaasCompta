#!/bin/bash
set -e

echo "======================================="
echo " DEMARRAGE - Universal Invest Strategy"
echo " Environnement : ${APP_ENV:-production}"
echo "======================================="

cd /var/www/html

# ── 1. Lien symbolique storage ───────────────────────────────
echo "[1/7] Lien storage:link..."
php artisan storage:link --force || true

# ── 2. Vider le cache existant ───────────────────────────────
echo "[2/7] Nettoyage du cache..."
php artisan config:clear  || true
php artisan route:clear   || true
php artisan view:clear    || true
php artisan cache:clear   || true

# ── 3. Générer la clé APP si absente ────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "[3/7] Génération de APP_KEY..."
    php artisan key:generate --force
else
    echo "[3/7] APP_KEY déjà défini. OK."
fi

# ── 4. Migrations ────────────────────────────────────────────
echo "[4/7] Migration de la base de données..."
php artisan migrate --force --no-interaction

# ── 5. Mise en cache pour la production ──────────────────────
echo "[5/7] Mise en cache config/routes/vues..."
php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true

# ── 6. Scheduler (tâche cron en arrière-plan) ────────────────
echo "[6/7] Démarrage du scheduler Laravel en arrière-plan..."
while true; do
    php /var/www/html/artisan schedule:run --no-interaction >> /var/log/scheduler.log 2>&1
    sleep 60
done &

# ── 7. Démarrer Apache ───────────────────────────────────────
echo "[7/7] Démarrage d'Apache..."
exec apache2-foreground
