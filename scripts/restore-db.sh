#!/usr/bin/env bash
# ==============================================================================
# AI BUILDS MY WEBSITE — MYSQL DISASTER RECOVERY & RESTORE SCRIPT
# ==============================================================================
# Usage:
#   ./scripts/restore-db.sh <path_to_backup.sql.gz>
# Example:
#   ./scripts/restore-db.sh /var/backups/mysql/backup_ai_builds_my_website_20261125_020000.sql.gz
# ==============================================================================

set -euo pipefail

if [ $# -lt 1 ]; then
    echo "Usage: $0 <path_to_backup_file.sql.gz>" >&2
    exit 1
fi

BACKUP_FILE="$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "ERROR: Backup file '$BACKUP_FILE' does not exist." >&2
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(dirname "$SCRIPT_DIR")"

# Source environment variables if .env exists
if [ -f "$APP_DIR/.env" ]; then
    DB_HOST=$(grep -E '^DB_HOST=' "$APP_DIR/.env" | cut -d '=' -f2- | tr -d '"' | tr -d "'")
    DB_PORT=$(grep -E '^DB_PORT=' "$APP_DIR/.env" | cut -d '=' -f2- | tr -d '"' | tr -d "'")
    DB_DATABASE=$(grep -E '^DB_DATABASE=' "$APP_DIR/.env" | cut -d '=' -f2- | tr -d '"' | tr -d "'")
    DB_USERNAME=$(grep -E '^DB_USERNAME=' "$APP_DIR/.env" | cut -d '=' -f2- | tr -d '"' | tr -d "'")
    DB_PASSWORD=$(grep -E '^DB_PASSWORD=' "$APP_DIR/.env" | cut -d '=' -f2- | tr -d '"' | tr -d "'")
else
    echo "ERROR: .env file not found at $APP_DIR/.env" >&2
    exit 1
fi

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-ai_builds_my_website}"

echo "=============================================================================="
echo "WARNING: YOU ARE ABOUT TO OVERWRITE DATABASE '$DB_DATABASE' ON $DB_HOST:$DB_PORT"
echo "Backup File: $BACKUP_FILE"
echo "=============================================================================="

export MYSQL_PWD="$DB_PASSWORD"

echo "Decompressing and importing SQL backup..."
gunzip < "$BACKUP_FILE" | mysql \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USERNAME" \
    "$DB_DATABASE"

unset MYSQL_PWD

echo "Database restoration completed successfully."
echo "Running post-restore optimization and clear caches..."
cd "$APP_DIR"
php artisan cache:clear
php artisan view:clear
php artisan config:clear
echo "Application ready."
