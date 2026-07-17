#!/usr/bin/env bash
# ────────────────────────────────────────────────
# Nyalife HMS — Post-Deploy Commands (via SSH)
# ────────────────────────────────────────────────

set -e

APP_DIR="/home1/nyalifew/nyalife_core"

echo "📂 Navigating to application directory..."
cd "$APP_DIR"

echo "🔧 Pulling latest code..."
git pull origin main

if ! command -v composer &> /dev/null; then
    echo "⚠️ Global composer command not found. Checking for local composer.phar..."
    if [ ! -f "composer.phar" ]; then
        echo "📥 Downloading Composer installer..."
        curl -sS https://getcomposer.org/installer | php
    fi
    COMPOSER_CMD="php composer.phar"
else
    COMPOSER_CMD="composer"
fi

$COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction 2>&1

echo "🗄️ Running migrations..."
php artisan migrate --force

echo "🗄️ Migrating production data from legacy..."
php artisan production:migrate-data 2>&1 || echo "⚠️ Data migration skipped (already done or legacy DB not available)"

echo "🔧 Optimizing caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "🔒 Fixing storage and cache permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "✅ Post-deployment complete!"
