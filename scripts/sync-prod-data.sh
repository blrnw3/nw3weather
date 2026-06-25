#!/usr/bin/env bash
#
# Pull the live/generated data files from the production server into the local
# working tree so the Docker site renders with real data.
#
# This is STRICTLY one-way (prod -> local). It never writes to prod.
#
# Usage:
#   scripts/sync-prod-data.sh                 # one-shot pull of data + generated files
#   scripts/sync-prod-data.sh --full          # also pull jpgraph/, static-images/, sample cam/video
#   scripts/sync-prod-data.sh --loop [secs]   # keep clientraw.txt fresh (default 20s) to mimic the live feed
#
# Config (override via env or by editing the defaults below):
#   PROD_SSH        ssh target          (default ben@188.166.156.109)
#   PROD_SSH_PORT   ssh port            (default 8294)
#   PROD_DOCROOT    remote docroot      (default /var/www/html)
#   LOCAL_DOCROOT   local docroot       (default <repo>/SiteV3)
set -euo pipefail

# --- config -----------------------------------------------------------------
PROD_SSH="${PROD_SSH:-ben@188.166.156.109}"
PROD_SSH_PORT="${PROD_SSH_PORT:-8294}"
PROD_DOCROOT="${PROD_DOCROOT:-/var/www/html}"
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LOCAL_DOCROOT="${LOCAL_DOCROOT:-$REPO_ROOT/SiteV3}"

RSYNC_SSH=(-e "ssh -p ${PROD_SSH_PORT}")
SRC="$PROD_SSH:$PROD_DOCROOT/"

# Patterns for the data + cron-generated files that are NOT in git.
# (*Tags.php matches generated tag files like RainTags.php, not cron_tags.php.)
DATA_FILTERS=(
  --include='clientraw.txt'
  --include='clientrawBackup.txt'
  --include='customtextout.txt'
  --include='goodlog.txt'
  --include='todaylog.txt'
  --include='*.csv'
  --include='serialised_*.txt'
  --include='*Tags.php'
  --include='METAR.txt'
  --include='WUforecast.txt'
  --include='pm25_latest.txt'
  --include='*.json'
  --include='logfiles/'
  --include='logfiles/daily/'
  --include='logfiles/daily/*'
  --include='photos/'
  --include='photos/albums/'
  --include='photos/albums/albInfo.php'
  --exclude='*'
)

mkdir -p "$LOCAL_DOCROOT"

sync_data() {
  echo ">> Pulling data/generated files from $SRC"
  rsync -avz --prune-empty-dirs "${RSYNC_SSH[@]}" "${DATA_FILTERS[@]}" "$SRC" "$LOCAL_DOCROOT/"
}

sync_full_extras() {
  echo ">> Pulling jpgraph/ (chart library)"
  rsync -avz "${RSYNC_SSH[@]}" "$PROD_SSH:$PROD_DOCROOT/jpgraph/" "$LOCAL_DOCROOT/jpgraph/" || \
    echo "   (skipped: jpgraph not found at $PROD_DOCROOT/jpgraph)"

  echo ">> Pulling static-images/"
  rsync -avz "${RSYNC_SSH[@]}" "$PROD_SSH:$PROD_DOCROOT/static-images/" "$LOCAL_DOCROOT/static-images/" || \
    echo "   (skipped: static-images not found)"

  echo ">> Pulling recent generated graphs / cam images"
  rsync -avz "${RSYNC_SSH[@]}" \
    --include='*.png' --include='*.gif' --include='*.jpg' --exclude='*' \
    "$SRC" "$LOCAL_DOCROOT/"

  echo ">> Pulling photo album thumbnails (preview images for wx7)"
  rsync -avz --prune-empty-dirs "${RSYNC_SSH[@]}" \
    --include='*/' --include='*s.jpg' --include='*s.JPG' --exclude='*' \
    "$PROD_SSH:$PROD_DOCROOT/photos/" "$LOCAL_DOCROOT/photos/" || \
    echo "   (skipped: photos not found)"
}

loop_clientraw() {
  local interval="${1:-20}"
  echo ">> Live loop: refreshing clientraw.txt + datNow every ${interval}s (Ctrl-C to stop)"
  while true; do
    rsync -az "${RSYNC_SSH[@]}" "$PROD_SSH:$PROD_DOCROOT/clientraw.txt" "$LOCAL_DOCROOT/clientraw.txt" || true
    rsync -az "${RSYNC_SSH[@]}" "$PROD_SSH:$PROD_DOCROOT/serialised_datNow.txt" "$LOCAL_DOCROOT/" 2>/dev/null || true
    sleep "$interval"
  done
}

case "${1:-}" in
  --loop) loop_clientraw "${2:-20}" ;;
  --full) sync_data; sync_full_extras ;;
  "")     sync_data ;;
  *)      echo "Unknown option: $1"; sed -n '2,20p' "$0"; exit 1 ;;
esac

echo ">> Done."
