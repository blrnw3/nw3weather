#!/usr/bin/env bash
# Cron wrapper for site/sun_model/classify.py (webcam sunshine). NOT INSTALLED - suggested crontab (user ben):
#   */5 * * * * /var/www/html/../scripts/run_sun_classify.sh      (or wherever this script is deployed)
# Single instance (flock), low priority (nice/ionice), capped memory; uses the per-user venv ~/sunenv
# (python3.6 + numpy 1.19.5 + Pillow 8.4.0). Extra args pass through (e.g. --dry-run, -d 2026-10-15).
# Overridable: NW3_SITE_ROOT, SUN_VENV, SUN_OUT, SUN_FRAMES, SUN_LOG, SUN_MEM_KB.
set -euo pipefail

SITE="${NW3_SITE_ROOT:-/var/www/html}"
VENV="${SUN_VENV:-$HOME/sunenv}"
OUT="${SUN_OUT:-$SITE/sunlog}"
FRAMES="${SUN_FRAMES:-/mnt/webcam/html/camchive/hik}"
LOG="${SUN_LOG:-$HOME/log/sun_classify.log}"
LOCK="${SUN_LOCK:-/tmp/nw3_sun_classify.lock}"
MEM_KB="${SUN_MEM_KB:-600000}"     # virtual memory cap; the job peaks ~50 MB RSS

mkdir -p "$(dirname "$LOG")"
exec 9>"$LOCK"
if ! flock -n 9; then
    echo "$(date -u +%FT%TZ) previous sun_classify still running; skipping" >> "$LOG"
    exit 0
fi
if [[ -f "$LOG" && $(stat -c %s "$LOG") -gt 5242880 ]]; then mv -f "$LOG" "$LOG.1"; fi

export PYTHONPATH="$SITE" OPENBLAS_NUM_THREADS=1 OMP_NUM_THREADS=1 MKL_NUM_THREADS=1
ulimit -v "$MEM_KB"
cd "$SITE"
args=(--frames "$FRAMES" --out "$OUT")
# shortly after midnight also finish yesterday (its last minutes settle on the next run)
if [[ "$(date +%H%M)" < "0030" ]]; then
    nice -n 15 ionice -c3 timeout 600 "$VENV/bin/python" -m sun_model.classify -d "$(date -d yesterday +%F)" "${args[@]}" "$@" >> "$LOG" 2>&1 || true
fi
exec nice -n 15 ionice -c3 timeout 600 "$VENV/bin/python" -m sun_model.classify "${args[@]}" "$@" >> "$LOG" 2>&1
