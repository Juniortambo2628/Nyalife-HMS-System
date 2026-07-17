#!/bin/bash
# Complete script to import production data into new schema
# Run this locally to create a clean SQL file for production

set -e

echo "🔄 Importing production data into new schema..."

# Configuration
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_USER="nyalifew"
DB_PASS="your_password"
DUMP_FILE="production_database_15_7_26.sql"
LEGACY_DB="nyalife_legacy"
NEW_DB="nyalife_new_schema"

echo "📋 Step 1: Create legacy database and import dump"
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS nyalife_legacy;"
mysql -u nyalifew -p nyalife_legacy < production_database_15_7_26.sql

echo "📋 Step 2: Create fresh new schema database"
mysql -u root -p -e "DROP DATABASE IF EXISTS nyalife_new_schema; CREATE DATABASE nyalife_new_schema;"

echo "📋 Step 3: Run migrations on new database"
# Temporarily switch .env to use new database
cp .env .env.backup
sed -i 's/DB_DATABASE=.*/DB_DATABASE=nyalife_new_schema/' .env
php artisan migrate --force

echo "📋 Step 4: Import legacy data into new schema"
php artisan legacy:import

echo "📋 Step 5: Export clean SQL for production"
php artisan legacy:export legacy-data-clean.sql

echo "📋 Step 6: Restore original .env"
mv .env.backup .env

echo "✅ Done! Clean SQL file: legacy-data-clean.sql"
echo "📤 Upload to production: mysql -u nyalifew -p nyalifew_core < legacy-data-clean.sql"