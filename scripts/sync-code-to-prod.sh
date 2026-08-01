#!/usr/bin/env bash
#
# Push site *code* from the local working tree to production.
# Never touches live/generated data, webcam stills, logs, or secrets.
#
# Default is a dry-run. Pass --go to write.
#
# Usage:
#   scripts/sync-code-to-prod.sh              # dry-run: show what would change
#   scripts/sync-code-to-prod.sh --go         # push code
#   scripts/sync-code-to-prod.sh --go --delete  # also remove remote files that
#                                               # are absent locally (dangerous;
#                                               # still never deletes excluded paths)
#
# Config (override via env):
#   PROD_SSH        ssh target          (default ben@188.166.156.109)
#   PROD_SSH_PORT   ssh port            (default 8294)
#   PROD_DOCROOT    remote docroot      (default /var/www/html)
#   LOCAL_DOCROOT   local site tree     (default <repo>/site)
set -euo pipefail

PROD_SSH="${PROD_SSH:-ben@188.166.156.109}"
PROD_SSH_PORT="${PROD_SSH_PORT:-8294}"
PROD_DOCROOT="${PROD_DOCROOT:-/var/www/html}"
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LOCAL_DOCROOT="${LOCAL_DOCROOT:-$REPO_ROOT/site}"

DO_PUSH=0
DO_DELETE=0
for arg in "$@"; do
	case "$arg" in
		--go) DO_PUSH=1 ;;
		--delete) DO_DELETE=1 ;;
		-h|--help)
			sed -n '2,24p' "$0"
			exit 0
			;;
		*)
			echo "Unknown option: $arg" >&2
			sed -n '2,24p' "$0" >&2
			exit 1
			;;
	esac
done

if [ ! -d "$LOCAL_DOCROOT" ]; then
	echo "Local docroot not found: $LOCAL_DOCROOT" >&2
	exit 1
fi

# Everything that is data, generated, or owned by the server — never overwrite
# or (with --delete) remove these on prod.
# Keep in sync with site/.gitignore and scripts/sync-prod-data.sh.
EXCLUDES=(
	# Live / cron-generated text + caches
	--exclude='clientraw.txt'
	--exclude='clientrawBackup.txt'
	--exclude='customtextout.txt'
	--exclude='goodlog.txt'
	--exclude='todaylog.txt'
	--exclude='*.csv'
	--exclude='serialised_*.txt'
	--include='cron_tags.php'
	--exclude='*Tags.php'
	--exclude='METAR.txt'
	--exclude='WUforecast.txt'
	--exclude='pm25_latest.txt'
	--exclude='windy_widget.txt'
	--exclude='*.json'
	--exclude='api_latest.txt'
	--exclude='EXT_*.txt'
	--exclude='EXT_*.json'

	# Generated / uploaded imagery and video
	--exclude='*.png'
	--exclude='*.gif'
	--exclude='*.jpg'
	--exclude='*.jpeg'
	--exclude='*.wmv'
	--exclude='*.mp4'

	# Server-owned directories
	--exclude='logfiles/'
	--exclude='log/'
	--exclude='Logs/'
	--exclude='jpgraph/'
	--exclude='static-images/'
	--exclude='photos/'
	--exclude='camchive/'
	--exclude='oldSites/'
	--exclude='hampstead_data/'

	# Secrets stay on the server (template secrets.example.php is fine to push)
	--exclude='secrets.php'

	# Local / VCS noise
	--exclude='.git/'
	--exclude='.gitignore'
	--exclude='.DS_Store'
	--exclude='*.swp'
	--exclude='*~'
	--exclude='status.md'
)

RSYNC_OPTS=(-avz --human-readable)
if [ "$DO_DELETE" -eq 1 ]; then
	# Excluded paths are left alone on the remote (rsync default without
	# --delete-excluded).
	RSYNC_OPTS+=(--delete)
fi
if [ "$DO_PUSH" -eq 0 ]; then
	RSYNC_OPTS+=(--dry-run)
	echo ">> DRY RUN (pass --go to push). From: $LOCAL_DOCROOT/"
else
	echo ">> PUSHING code to $PROD_SSH:$PROD_DOCROOT/"
	if [ "$DO_DELETE" -eq 1 ]; then
		echo "   (--delete enabled: remote files absent locally will be removed,"
		echo "    excluding data/generated paths listed above)"
	fi
fi

rsync "${RSYNC_OPTS[@]}" \
	-e "ssh -p ${PROD_SSH_PORT}" \
	"${EXCLUDES[@]}" \
	"$LOCAL_DOCROOT/" \
	"$PROD_SSH:$PROD_DOCROOT/"

if [ "$DO_PUSH" -eq 0 ]; then
	echo ">> Dry-run complete. Re-run with --go to apply."
else
	echo ">> Done."
fi
