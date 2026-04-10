#!/bin/bash
# ────────────────────────────────────────────────
# Nyalife HMS — FIRST-TIME Server Setup
#
# Run this ONCE after SSH'ing into the server:
#   bash setup.sh
# ────────────────────────────────────────────────

set -e

APP_DIR="/home1/nyalifew/nyalife_core"
PUBLIC_HTML="/home1/nyalifew/public_html"

echo ""
echo "🏥 Nyalife HMS — First-time Setup"
echo "================================="
echo ""

# 1. Clone the repository
if [ ! -d "$APP_DIR" ]; then
    echo "📥 Cloning repository..."
    cd /home1/nyalifew
    git clone https://github.com/Juniortambo2628/Nyalife-HMS-System.git nyalife_core
else
    echo "📂 Repository already exists, pulling latest..."
    cd "$APP_DIR"
    git pull origin main
fi

cd "$APP_DIR"

# 2. Install PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction 2>&1

# 3. Create production .env
if [ ! -f "$APP_DIR/.env" ]; then
    echo "⚙️  Creating .env file..."
    cat > "$APP_DIR/.env" << 'ENVFILE'
APP_NAME="Nyalife HMS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://nyalifewomensclinic.net

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=single
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nyalifew_core
DB_USERNAME=nyalifew_core
DB_PASSWORD=Nyalife_core@2026

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.nyalifewomensclinic.net
SESSION_SECURE_COOKIE=true

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file

MAIL_MAILER=smtp
MAIL_HOST=mail.nyalifewomensclinic.net
MAIL_PORT=465
MAIL_USERNAME=hms@nyalifewomensclinic.net
MAIL_PASSWORD=Nyalife_core@2026
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="hms@nyalifewomensclinic.net"
MAIL_FROM_NAME="Nyalife Women's Clinic"

VITE_APP_NAME="Nyalife HMS"
ENVFILE
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate
else
    echo "⚙️  .env already exists, skipping..."
fi

# 4. Create storage directories
echo "📁 Creating storage directories..."
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# 5. Set permissions
echo "🔒 Setting permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# 6. Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# 7. Seed medications & lab tests
echo "💊 Seeding medications and lab tests from Excel..."
php artisan db:seed --class=LabAndMedicationSeeder --force

# 8. Setup public_html bridge
echo "🌐 Setting up public_html..."

# Back up existing public_html content
if [ -d "$PUBLIC_HTML" ] && [ ! -L "$PUBLIC_HTML" ]; then
    # Copy Laravel public files to public_html
    cp -r "$APP_DIR/public/build" "$PUBLIC_HTML/build" 2>/dev/null || true
    cp -r "$APP_DIR/public/assets" "$PUBLIC_HTML/assets" 2>/dev/null || true
    cp -f "$APP_DIR/public/favicon.ico" "$PUBLIC_HTML/favicon.ico" 2>/dev/null || true
    cp -f "$APP_DIR/public/robots.txt" "$PUBLIC_HTML/robots.txt" 2>/dev/null || true

    # Create bridge index.php
    cat > "$PUBLIC_HTML/index.php" << 'INDEXFILE'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$basePath = '/home1/nyalifew/nyalife_core';

if (file_exists($maintenance = $basePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $basePath . '/vendor/autoload.php';

/** @var Application $app */
$app = require_once $basePath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
INDEXFILE

    # Create .htaccess
    cat > "$PUBLIC_HTML/.htaccess" << 'HTACCESSFILE'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)/$ /$1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

<FilesMatch "\.(env|log|json)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
HTACCESSFILE
fi

# 9. Create storage symlink
if [ ! -L "$PUBLIC_HTML/storage" ]; then
    echo "🔗 Creating storage symlink..."
    ln -sf "$APP_DIR/storage/app/public" "$PUBLIC_HTML/storage"
fi

# 10. Cache everything
echo "🔧 Optimizing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "════════════════════════════════════════"
echo "✅ SETUP COMPLETE!"
echo ""
echo "  🌐 Visit: https://nyalifewomensclinic.net"
echo "  📧 Mail:  hms@nyalifewomensclinic.net"
echo ""
echo "  For future deploys, run:"
echo "  bash $APP_DIR/deploy.sh"
echo "════════════════════════════════════════"
echo ""
