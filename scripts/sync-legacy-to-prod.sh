#!/usr/bin/env bash
# Push the maintained v3 archive code. Runtime caches remain server-owned.
# Dry-run by default; pass --go to write.
set -euo pipefail

PROD_SSH="${PROD_SSH:-ben@188.166.156.109}"
PROD_SSH_PORT="${PROD_SSH_PORT:-8294}"
PROD_DOCROOT="${PROD_DOCROOT:-/var/www/html}"
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SOURCE="$REPO_ROOT/oldSites/sitev3/"
TARGET="$PROD_SSH:$PROD_DOCROOT/oldSites/sitev3/"

OPTS=(-az --omit-dir-times --human-readable --out-format='%n')
if [[ "${1:-}" != "--go" ]]; then
	OPTS+=(--dry-run)
	echo ">> DRY RUN (pass --go to push legacy code)"
else
	echo ">> PUSHING legacy v3 code"
fi

rsync "${OPTS[@]}" \
	-e "ssh -p ${PROD_SSH_PORT}" \
	--exclude='*.csv' \
	--exclude='serialised_*' \
	--exclude='*Tags.php' \
	--exclude='*.jpg' --exclude='*.jpeg' --exclude='*.png' --exclude='*.gif' \
	"$SOURCE" "$TARGET"
