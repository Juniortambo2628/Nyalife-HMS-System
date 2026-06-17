#!/usr/bin/env bash
# ────────────────────────────────────────────────
# Nyalife HMS — Post-Deploy Commands (via SSH)
# ────────────────────────────────────────────────

set -e

echo "📂 Navigating to application directory..."
cd /home1/nyalifew/nyalife_core

echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction 2>&1

echo "🗄️ Running migrations..."
php artisan migrate --force

echo "🔧 Optimizing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "🔒 Fixing storage and cache permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "✅ Post-deployment complete!"
