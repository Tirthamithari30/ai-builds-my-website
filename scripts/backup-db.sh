#!/usr/bin/env bash
# ==============================================================================
# AI BUILDS MY WEBSITE — AUTOMATED MYSQL BACKUP SCRIPT
# ==============================================================================
# Usage:
#   ./scripts/backup-db.sh [backup_dir]
# Example cron schedule (Runs daily at 2:00 AM):
#   0 2 * * * /var/www/ai-builds-my-website/scripts/backup-db.sh /var/backups/mysql >> /var/log/db-backup.log 2>&1
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="${1:-$APP_DIR/storage/backups}"
TIMESTAMP="$(date +"%Y%m%d_%H%M%S")"
RETENTION_DAYS=14

# Ensure backup directory exists
mkdir -p "$BACKUP_DIR"

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

BACKUP_FILE="$BACKUP_DIR/backup_${DB_DATABASE}_${TIMESTAMP}.sql.gz"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Starting MySQL backup for database: '$DB_DATABASE'..."

# Execute mysqldump with single-transaction, quick, and gzip compression
export MYSQL_PWD="$DB_PASSWORD"
mysqldump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USERNAME" \
    --single-transaction \
    --quick \
    --routines \
    --triggers \
    "$DB_DATABASE" | gzip -9 > "$BACKUP_FILE"

unset MYSQL_PWD

FILESIZE=$(du -h "$BACKUP_FILE" | cut -f1)
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backup created successfully: $BACKUP_FILE ($FILESIZE)"

# Rotate older backups older than RETENTION_DAYS
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Purging backups older than $RETENTION_DAYS days..."
find "$BACKUP_DIR" -type f -name "backup_${DB_DATABASE}_*.sql.gz" -mtime +"$RETENTION_DAYS" -exec rm -f {} \;

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backup job complete."
