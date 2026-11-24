#!/usr/bin/env bash
# ==============================================================================
# AI BUILDS MY WEBSITE — ZERO-DOWNTIME PRODUCTION DEPLOYMENT SCRIPT
# ==============================================================================
# Usage:
#   ./scripts/deploy.sh
# Runs on production server upon receiving git release tag or main branch update.
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(dirname "$SCRIPT_DIR")"

cd "$APP_DIR"

echo "=============================================================================="
echo "Starting Production Deployment: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=============================================================================="

# 1. Put application into maintenance mode with bypass secret
DEPLOY_SECRET="${DEPLOY_SECRET:-deploy-bypass-token}"
echo "[1/8] Activating maintenance mode..."
php artisan down --secret="$DEPLOY_SECRET" --render="errors.500" || true

# 2. Pull latest code
echo "[2/8] Pulling latest production code from git..."
git pull origin main --ff-only

# 3. Install/Update PHP Dependencies (optimized for production)
echo "[3/8] Installing Composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 4. Compile frontend assets
echo "[4/8] Building minified frontend assets with Vite..."
npm ci --prefer-offline --no-audit
npm run build

# 5. Run Database Migrations
echo "[5/8] Running database migrations with force flag..."
php artisan migrate --force

# 6. Ensure Storage Symbolic Link
echo "[6/8] Ensuring storage symlink..."
php artisan storage:link || true

# 7. Optimize & Cache Configuration, Routes, Views, and Events
echo "[7/8] Caching configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. Restart PHP-FPM / Queue Workers and Bring App Up
echo "[8/8] Deactivating maintenance mode and refreshing services..."
php artisan queue:restart || true
php artisan up

echo "=============================================================================="
echo "Deployment completed successfully at: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=============================================================================="
