#!/usr/bin/env bash
# Installs a daily cron job (03:00) that runs bin/backup.sh, logging to
# ~/koval-backups/backup.log. Safe to re-run — replaces any existing
# koval-backup line instead of duplicating it.
set -euo pipefail

PROJECT_DIR="/home/guru/law-firm-test"
CRON_LINE="0 3 * * * /usr/bin/flock -n /tmp/koval-backup.lock $PROJECT_DIR/bin/backup.sh >> /home/guru/koval-backups/backup.log 2>&1 # koval-backup"

( crontab -l 2>/dev/null | grep -v '# koval-backup' || true ; echo "$CRON_LINE" ) | crontab -

echo "Installed. Current crontab:"
crontab -l
