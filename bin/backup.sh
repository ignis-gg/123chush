#!/usr/bin/env bash
#
# KOVAL Legal — full backup: database + uploads.
#
# Two-tier design, because a single "backup lives next to the project"
# scheme is exactly what already failed once (2026-09-02, whole project
# directory deleted by accident):
#
#   1. LOCAL backups (~/koval-backups/) — outside the project directory
#      entirely, so a repeat of that incident (rm -rf/rmtree on
#      law-firm-test) can't take these out too. Rotated, keeps the last
#      KEEP_DAYS days.
#   2. OFF-MACHINE copy — the DB dump (small, text, compresses well) is
#      also committed + pushed to the project's own GitHub repo, under
#      backups/db/. This survives even if the whole machine/disk is
#      lost, using infra we already have working (git push). Uploads are
#      NOT pushed to git — they're binary and will grow over time as
#      real client files accumulate; local rotation only for those. If
#      real off-machine safety for uploads matters later, that needs
#      actual cloud storage (S3/B2/etc.) with its own credentials — ask
#      the user before setting that up.
#
# Run manually any time: bash bin/backup.sh
# Runs automatically via cron — see bin/install-cron.sh.

set -euo pipefail

PROJECT_DIR="/home/guru/law-firm-test"
LOCAL_BACKUP_DIR="/home/guru/koval-backups"
GIT_BACKUP_DIR="$PROJECT_DIR/backups/db"
KEEP_DAYS=14
STAMP="$(date +%Y%m%d-%H%M%S)"

cd "$PROJECT_DIR"

echo "== [$STAMP] Starting backup =="

# --- 1. Database dump ---
DB_DUMP_LOCAL="$LOCAL_BACKUP_DIR/db/law-firm-db-${STAMP}.sql.gz"
sg docker -c "docker exec ddev-law-firm-db mysqldump -u root -proot db" | gzip > "$DB_DUMP_LOCAL"
echo "DB dump -> $DB_DUMP_LOCAL ($(du -h "$DB_DUMP_LOCAL" | cut -f1))"

# --- 2. Uploads archive (local only — see note above) ---
if [ -d "$PROJECT_DIR/wp-content/uploads" ] && [ -n "$(find "$PROJECT_DIR/wp-content/uploads" -type f -print -quit)" ]; then
  UPLOADS_ARCHIVE="$LOCAL_BACKUP_DIR/uploads/uploads-${STAMP}.tar.gz"
  tar -czf "$UPLOADS_ARCHIVE" -C "$PROJECT_DIR/wp-content" uploads
  echo "Uploads archive -> $UPLOADS_ARCHIVE ($(du -h "$UPLOADS_ARCHIVE" | cut -f1))"
else
  echo "Uploads dir empty or missing, skipping archive."
fi

# --- 3. Rotate local backups (keep last $KEEP_DAYS days) ---
find "$LOCAL_BACKUP_DIR/db" -name '*.sql.gz' -mtime "+${KEEP_DAYS}" -delete
find "$LOCAL_BACKUP_DIR/uploads" -name '*.tar.gz' -mtime "+${KEEP_DAYS}" -delete

# --- 4. Off-machine copy: commit + push the DB dump to git ---
mkdir -p "$GIT_BACKUP_DIR"
cp "$DB_DUMP_LOCAL" "$GIT_BACKUP_DIR/latest.sql.gz"

# Keep the last 7 dated dumps in git too (not just "latest"), so there's
# some history to roll back through, without letting the repo grow
# unbounded.
cp "$DB_DUMP_LOCAL" "$GIT_BACKUP_DIR/law-firm-db-${STAMP}.sql.gz"
ls -1t "$GIT_BACKUP_DIR"/law-firm-db-*.sql.gz 2>/dev/null | tail -n +8 | xargs -r rm --

cd "$PROJECT_DIR"
if ! git diff --quiet --exit-code -- backups/db 2>/dev/null || [ -n "$(git status --porcelain -- backups/db)" ]; then
  git add backups/db
  git commit -m "Automated DB backup ${STAMP}" >/dev/null
  git push origin master >/dev/null 2>&1 && echo "Pushed backup commit to GitHub." || echo "WARNING: git push failed — backup committed locally only."
else
  echo "DB dump unchanged since last backup, nothing new to commit."
fi

echo "== [$STAMP] Backup complete =="
