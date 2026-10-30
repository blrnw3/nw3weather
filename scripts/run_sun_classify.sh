#!/usr/bin/env bash
# Cron wrapper for site/sun_model/classify.py (webcam sunshine). NOT INSTALLED - suggested crontab (user ben):
#   */5 * * * * /srv/nw3weather/repo/scripts/run_sun_classify.sh   (the deploy checkout; site code runs from the docroot)
# Single instance (flock), low priority (nice/ionice), capped memory; uses the per-user venv ~/sunenv
# (python3 -m venv ~/sunenv && ~/sunenv/bin/pip install -r scripts/sun_requirements.txt).
# Frames: the 1-min local camchive under CAM_ROOT (cron_cam.php). Output: docroot sunlog/ (group nw3web, setgid).
# Extra args pass through (e.g. --dry-run, -d 2026-10-15).
# Overridable: NW3_SITE_ROOT, SUN_VENV, SUN_OUT, SUN_FRAMES, SUN_LOG, SUN_MEM_KB.
set -euo pipefail

SITE="${NW3_SITE_ROOT:-/var/www/html}"
VENV="${SUN_VENV:-$HOME/sunenv}"
OUT="${SUN_OUT:-$SITE/sunlog}"
FRAMES="${SUN_FRAMES:-/srv/nw3weather/cam/stills/camchive/hik}"
LOG="${SUN_LOG:-$HOME/log/sun_classify.log}"
LOCK="${SUN_LOCK:-/tmp/nw3_sun_classify.lock}"
MEM_KB="${SUN_MEM_KB:-600000}"     # virtual memory cap; the job peaks ~60 MB RSS

umask 002                           # sunlog files stay group-writable for the site user
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
