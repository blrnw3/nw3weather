#!/usr/bin/env bash
# nw3-cache-prune: delete stale file-cache entries under
#   /var/www/html/cache/v5/rankperiods
#   /var/www/html/cache/v5/reports   (PR #107; may be absent until that ships)
#
# Those JSON files are a short-TTL (10–30 min) miss-regenerated cache keyed by
# query params. Nothing expects them to be permanent. This script never touches
# cache/v5 root (serialised_*, forecast_*, nearby.json, rank_flags, …), never
# touches cache/v5/locks, and never follows symlinks.
#
# Age pass: regular files with mtime older than MAX_AGE_DAYS (default 2).
# Cap pass: if a target dir's regular files still exceed its cap (default
# 200 MiB each), delete oldest-by-atime, then mtime, until under the cap.
# Files touched in the last SKIP_FRESH_SECS (default 60) are left alone so a
# writer doing pid-tmp + rename is not raced. Empty subdirectories are removed
# afterwards; the rankperiods/ and reports/ directories themselves are kept.
#
# Usage: nw3-cache-prune.sh [--dry-run]
# systemd: nw3-cache-prune.timer (hourly at :30 London, as user nw3).
# Hourly, not daily: crawlers have been adding ~350 MiB/day, which would blow
# a 200 MiB cap between daily 04:30 runs. A 2-day age window alone still leaves
# ~685 MiB today; the cap pass is what holds the line.
#
# Install (as root), from a checkout of this repo:
#   sudo install -o root -g root -m 755 scripts/hardening/nw3-cache-prune.sh /usr/local/sbin/nw3-cache-prune
#   sudo install -o root -g root -m 644 scripts/hardening/nw3-cache-prune.service \
#     scripts/hardening/nw3-cache-prune.timer /etc/systemd/system/
#   sudo systemctl daemon-reload
#   sudo systemd-analyze verify /etc/systemd/system/nw3-cache-prune.service \
#     /etc/systemd/system/nw3-cache-prune.timer
#   sudo systemctl enable --now nw3-cache-prune.timer
# Status / logs:
#   systemctl status nw3-cache-prune.timer nw3-cache-prune.service
#   systemctl list-timers nw3-cache-prune.timer
#   journalctl -u nw3-cache-prune.service -n 20 --no-pager
#   grep nw3-cache-prune /var/log/syslog
# A one-shot prune: sudo systemctl start nw3-cache-prune.service
# Dry-run (read-only; any user who can read the cache dirs):
#   /usr/local/sbin/nw3-cache-prune --dry-run
#
# Uninstall:
#   sudo systemctl disable --now nw3-cache-prune.timer
#   sudo rm -f /usr/local/sbin/nw3-cache-prune \
#     /etc/systemd/system/nw3-cache-prune.service \
#     /etc/systemd/system/nw3-cache-prune.timer
#   sudo systemctl daemon-reload
set -euo pipefail

DRY=0
case "$#:${1:-}" in
  0:) ;;
  1:--dry-run) DRY=1 ;;
  *) echo "usage: $0 [--dry-run]" >&2; exit 2 ;;
esac

CACHE_ROOT=${NW3_CACHE_PRUNE_ROOT:-/var/www/html/cache/v5}
MAX_AGE_DAYS=${MAX_AGE_DAYS:-2}
RANKPERIODS_CAP_BYTES=${RANKPERIODS_CAP_BYTES:-$((200 * 1024 * 1024))}
REPORTS_CAP_BYTES=${REPORTS_CAP_BYTES:-$((200 * 1024 * 1024))}
SKIP_FRESH_SECS=${SKIP_FRESH_SECS:-60}
LOCK_FILE=${NW3_CACHE_PRUNE_LOCK:-/var/lib/nw3/nw3-cache-prune.lock}

LIVE_ROOT=/var/www/html/cache/v5
me=$(id -un)

renice -n 19 $$ >/dev/null 2>&1 || true
ionice -c 3 -p $$ >/dev/null 2>&1 || ionice -c 2 -n 7 -p $$ >/dev/null 2>&1 || true

is_uint() { [[ "$1" =~ ^[0-9]+$ ]]; }
is_uint "$MAX_AGE_DAYS" && [ "$MAX_AGE_DAYS" -ge 1 ] || { echo "MAX_AGE_DAYS must be an integer >= 1" >&2; exit 2; }
is_uint "$RANKPERIODS_CAP_BYTES" || { echo "RANKPERIODS_CAP_BYTES must be an integer" >&2; exit 2; }
is_uint "$REPORTS_CAP_BYTES" || { echo "REPORTS_CAP_BYTES must be an integer" >&2; exit 2; }
is_uint "$SKIP_FRESH_SECS" || { echo "SKIP_FRESH_SECS must be an integer" >&2; exit 2; }

# Resolve CACHE_ROOT; refuse a symlink so we cannot be pointed at another tree.
[ -d "$CACHE_ROOT" ] && [ ! -L "$CACHE_ROOT" ] || { echo "no dir $CACHE_ROOT" >&2; exit 1; }
CACHE_ROOT=$(cd "$CACHE_ROOT" && pwd -P)

if [ "$CACHE_ROOT" = "$LIVE_ROOT" ]; then
  if [ "$me" != root ] && [ "$me" != nw3 ]; then
    if [ "$DRY" != 1 ] && [ "${NW3_CACHE_PRUNE_TEST:-}" != 1 ]; then
      echo "live prune must run as nw3 or root (or pass --dry-run)" >&2
      exit 1
    fi
  fi
  if [ "${NW3_CACHE_PRUNE_TEST:-}" = 1 ]; then
    echo "test mode refuses the live dir $LIVE_ROOT" >&2
    exit 1
  fi
else
  # Test / override root must not resolve to the live tree (symlink tricks).
  if [ "$CACHE_ROOT" = "$LIVE_ROOT" ]; then
    echo "refusing live cache root" >&2
    exit 1
  fi
fi

# Lock: prefer the configured path; dry-run as a non-nw3 user may not be able
# to create /var/lib/nw3/..., so fall back to /tmp.
if [ ! -w "$(dirname "$LOCK_FILE")" ] 2>/dev/null; then
  LOCK_FILE=${TMPDIR:-/tmp}/nw3-cache-prune.lock
fi
touch "$LOCK_FILE" 2>/dev/null || { echo "cannot create lock $LOCK_FILE" >&2; exit 1; }
exec 9>"$LOCK_FILE"
if ! flock -n 9; then
  echo "another prune is running"
  exit 0
fi

now=$(date +%s)
age_cut=$((now - MAX_AGE_DAYS * 86400))
fresh_cut=$((now - SKIP_FRESH_SECS))

# Accumulate deletions in these globals.
del_files=0
del_bytes=0
age_files=0
age_bytes=0
cap_files=0
cap_bytes=0
fail_files=0
emptied_dirs=0

log_line() {
  local msg=$1
  echo "$msg"
  logger -t nw3-cache-prune -- "$msg" 2>/dev/null || true
}

# Confirm a target is a real directory exactly named $1 under CACHE_ROOT.
safe_target() {
  local name=$1
  local dir=$CACHE_ROOT/$name
  if [ ! -e "$dir" ]; then
    return 1
  fi
  if [ -L "$dir" ] || [ ! -d "$dir" ]; then
    echo "skipping $dir (not a real directory)" >&2
    return 1
  fi
  local resolved
  resolved=$(cd "$dir" && pwd -P)
  if [ "$resolved" != "$CACHE_ROOT/$name" ]; then
    echo "skipping $dir (resolves outside $CACHE_ROOT/$name)" >&2
    return 1
  fi
  # Never allow the cache root itself or a path that escapes it.
  case "$resolved" in
    "$CACHE_ROOT"/"$name") ;;
    *) echo "skipping $dir (unexpected path $resolved)" >&2; return 1 ;;
  esac
  printf '%s\n' "$resolved"
}

# List regular files as: atime mtime size relpath  (NUL-safe via find -print0 + stat in python-free awk)
# Paths are relative to $1 and must not contain /../ or start with /.
list_files() {
  local dir=$1
  # %A@ atime, %T@ mtime, %s size, %P path relative to the searched dir.
  # -P: never follow symlinks. -type f: regular files only (not symlinks).
  find -P "$dir" -mindepth 1 -type f -printf '%A@ %T@ %s %P\n'
}

is_safe_relpath() {
	# Reject empty, absolute, empty/dot/hidden path components, and "..".
	# Do not use a glob like *..* or *\n* here: an unquoted \n is just n,
	# and *n* would match every *.json name.
	local rel=$1 rest part
	[ -n "$rel" ] || return 1
	case "$rel" in
		/*) return 1 ;;
	esac
	rest=$rel
	while [ -n "$rest" ]; do
		case "$rest" in
			*/*) part=${rest%%/*}; rest=${rest#*/} ;;
			*) part=$rest; rest= ;;
		esac
		case "$part" in
			""|.|..|.*) return 1 ;;
		esac
	done
	return 0
}

delete_or_count() {
  local path=$1 size=$2
  if [ "$DRY" = 1 ]; then
    del_files=$((del_files + 1))
    del_bytes=$((del_bytes + size))
    return 0
  fi
  if rm -f -- "$path"; then
    if [ -e "$path" ]; then
      fail_files=$((fail_files + 1))
      return 1
    fi
    del_files=$((del_files + 1))
    del_bytes=$((del_bytes + size))
    return 0
  fi
  fail_files=$((fail_files + 1))
  return 1
}

# Print current sum of regular-file sizes in $1 (bytes).
dir_file_bytes() {
  local dir=$1
  find -P "$dir" -mindepth 1 -type f -printf '%s\n' | awk '{s+=$1} END {printf "%d", s+0}'
}

prune_dir() {
  local dir=$1
  local cap=$2
  local label=$3
  local line atime mtime size rel path
  local before_n before_b after_b
  local age_n=0 age_b=0 cap_n=0 cap_b=0

  before_n=$(find -P "$dir" -mindepth 1 -type f | wc -l)
  before_b=$(dir_file_bytes "$dir")

  # --- age pass ---
  while IFS= read -r line; do
    atime=${line%% *}; line=${line#* }
    mtime=${line%% *}; line=${line#* }
    size=${line%% *}; rel=${line#* }
    is_safe_relpath "$rel" || continue
    # mtime may be fractional; compare as integers via parameter expansion.
    local mt=${mtime%%.*}
    is_uint "$mt" && is_uint "$size" || continue
    if [ "$mt" -ge "$age_cut" ]; then
      continue
    fi
    path="$dir/$rel"
    [ -f "$path" ] && [ ! -L "$path" ] || continue
    if delete_or_count "$path" "$size"; then
      age_n=$((age_n + 1))
      age_b=$((age_b + size))
    fi
  done < <(list_files "$dir")

  age_files=$((age_files + age_n))
  age_bytes=$((age_bytes + age_b))

  # Remaining size: if dry-run, subtract what we would have deleted.
  if [ "$DRY" = 1 ]; then
    after_b=$((before_b - age_b))
  else
    after_b=$(dir_file_bytes "$dir")
  fi

  # --- cap pass ---
  if [ "$after_b" -gt "$cap" ]; then
    local need=$((after_b - cap))
    local got=0
    # Oldest atime first, then oldest mtime, then path for stability.
    while IFS= read -r line; do
      [ "$got" -ge "$need" ] && break
      atime=${line%% *}; line=${line#* }
      mtime=${line%% *}; line=${line#* }
      size=${line%% *}; rel=${line#* }
      is_safe_relpath "$rel" || continue
      local mt=${mtime%%.*} at=${atime%%.*}
      is_uint "$mt" && is_uint "$at" && is_uint "$size" || continue
      # Do not race a writer (pid-tmp or a just-renamed cache file).
      if [ "$mt" -ge "$fresh_cut" ]; then
        continue
      fi
      path="$dir/$rel"
      [ -f "$path" ] && [ ! -L "$path" ] || continue
      # In dry-run the age pass did not delete; skip files already counted as aged-out.
      if [ "$DRY" = 1 ] && [ "$mt" -lt "$age_cut" ]; then
        continue
      fi
      if delete_or_count "$path" "$size"; then
        cap_n=$((cap_n + 1))
        cap_b=$((cap_b + size))
        got=$((got + size))
      fi
    done < <(list_files "$dir" | LC_ALL=C sort -n -k1,1 -k2,2 -k4,4)
  fi

  cap_files=$((cap_files + cap_n))
  cap_bytes=$((cap_bytes + cap_b))

  # --- empty subdirs (not the target itself) ---
  if [ "$DRY" = 1 ]; then
    local ed
    ed=$(find -P "$dir" -mindepth 1 -depth -type d -empty 2>/dev/null | wc -l)
    emptied_dirs=$((emptied_dirs + ed))
  else
    while IFS= read -r -d '' d; do
      rmdir -- "$d" 2>/dev/null || true
      if [ ! -d "$d" ]; then
        emptied_dirs=$((emptied_dirs + 1))
      fi
    done < <(find -P "$dir" -mindepth 1 -depth -type d -empty -print0 2>/dev/null || true)
  fi

  local remain_b
  if [ "$DRY" = 1 ]; then
    remain_b=$((before_b - age_b - cap_b))
  else
    remain_b=$(dir_file_bytes "$dir")
  fi
  printf '%s before=%d files %d bytes; age_deleted=%d/%d bytes; cap_deleted=%d/%d bytes; remain=%d bytes\n' \
    "$label" "$before_n" "$before_b" "$age_n" "$age_b" "$cap_n" "$cap_b" "$remain_b" >&2
}

rp=$(safe_target rankperiods || true)
rep=$(safe_target reports || true)

if [ -n "$rp" ]; then
  prune_dir "$rp" "$RANKPERIODS_CAP_BYTES" rankperiods
else
  echo "rankperiods: absent, skipped" >&2
fi
if [ -n "$rep" ]; then
  prune_dir "$rep" "$REPORTS_CAP_BYTES" reports
else
  echo "reports: absent, skipped" >&2
fi

mb=$(awk -v b="$del_bytes" 'BEGIN { printf "%.1f", b/1048576 }')
action=deleted
[ "$DRY" = 1 ] && action='would delete'
summary=$(printf '%s %d files, freed %d bytes (%.1f MiB); age=%d/%d bytes cap=%d/%d bytes empty_dirs=%d failed=%d dry_run=%d root=%s' \
  "$action" "$del_files" "$del_bytes" "$mb" \
  "$age_files" "$age_bytes" "$cap_files" "$cap_bytes" \
  "$emptied_dirs" "$fail_files" "$DRY" "$CACHE_ROOT")
log_line "$summary"
[ "$fail_files" -eq 0 ]
