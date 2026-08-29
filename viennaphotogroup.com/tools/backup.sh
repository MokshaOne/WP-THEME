#!/usr/bin/env bash
# VPG · daily backup for shared hosting (DB dump + uploads sync, 14-day rotation)
# Fill in the four variables, place outside the webroot, run via hoster cron.
set -euo pipefail

DB_NAME="CHANGE_ME"
DB_USER="CHANGE_ME"
DB_PASS="CHANGE_ME"
WP_ROOT="$HOME/www"                 # path containing wp-content/
BACKUP_DIR="$HOME/backups"
KEEP_DAYS=14

STAMP="$(date +%Y-%m-%d)"
mkdir -p "$BACKUP_DIR/db" "$BACKUP_DIR/uploads"

# 1 · Database dump (gzipped)
mysqldump --single-transaction --quick \
  -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/db/vpg-$STAMP.sql.gz"

# 2 · Uploads · incremental mirror (photos, PDFs, covers)
rsync -a --delete "$WP_ROOT/wp-content/uploads/" "$BACKUP_DIR/uploads/"

# 3 · Rotate old dumps
find "$BACKUP_DIR/db" -name 'vpg-*.sql.gz' -mtime +"$KEEP_DAYS" -delete

echo "[$(date -Is)] backup ok · db=$(du -h "$BACKUP_DIR/db/vpg-$STAMP.sql.gz" | cut -f1) uploads=$(du -sh "$BACKUP_DIR/uploads" | cut -f1)"
