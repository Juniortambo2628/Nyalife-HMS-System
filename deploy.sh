#!/bin/bash
# ────────────────────────────────────────────────
# Nyalife HMS — Production Deploy Script
# 
# Usage:  bash /home1/nyalifew/nyalife_core/deploy.sh
# ────────────────────────────────────────────────

set -e

APP_DIR="/home1/nyalifew/nyalife_core"
PUBLIC_HTML="/home1/nyalifew/public_html"

echo ""
echo "🚀 Deploying Nyalife HMS..."
echo "   $(date)"
echo ""

cd "$APP_DIR"

# 1. Pull latest code from GitHub
echo "📥 Pulling latest from GitHub..."
git pull origin main

# 2. Install/update PHP dependencies (production only)
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction 2>&1

# 3. Run new database migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# 4. Clear and rebuild all caches
echo "🔧 Optimizing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Sync public assets to public_html (bridge file method)
echo "📁 Syncing public assets to public_html..."
# Copy build folder (Vite compiled JS/CSS)
cp -r "$APP_DIR/public/build" "$PUBLIC_HTML/build"
# Copy static assets (images, CSS, fonts)
cp -r "$APP_DIR/public/assets" "$PUBLIC_HTML/assets"
# Copy favicon and robots
cp -f "$APP_DIR/public/favicon.ico" "$PUBLIC_HTML/favicon.ico" 2>/dev/null || true
cp -f "$APP_DIR/public/robots.txt" "$PUBLIC_HTML/robots.txt" 2>/dev/null || true

# 6. Ensure storage link exists
if [ ! -L "$PUBLIC_HTML/storage" ]; then
    echo "🔗 Creating storage symlink..."
    ln -sf "$APP_DIR/storage/app/public" "$PUBLIC_HTML/storage"
fi

# 7. Fix permissions
echo "🔒 Setting permissions..."
chmod -R 775 "$APP_DIR/storage"
chmod -R 775 "$APP_DIR/bootstrap/cache"

echo ""
echo "✅ Deployment complete!"
echo "   Visit: https://nyalifewomensclinic.net"
echo "   $(date)"
echo ""
