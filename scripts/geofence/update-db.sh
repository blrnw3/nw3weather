#!/usr/bin/env bash
# Refresh the DB-IP Country Lite database used by mod_maxminddb.
# Safe to run as ben. Apache keeps the previous mmap until the next reload
# (certbot / graceful); country blocks stay in effect either way.
set -euo pipefail

DEST_DIR="${DEST_DIR:-/var/www/geoip}"
DEST="${DEST_DIR}/dbip-country-lite.mmdb"
YM="${YM:-$(date +%Y-%m)}"
URL="https://download.db-ip.com/free/dbip-country-lite-${YM}.mmdb.gz"

mkdir -p "$DEST_DIR"
TMP="$(mktemp "$DEST_DIR/.dbip.XXXXXX")"
trap 'rm -f "$TMP" "$TMP.gz"' EXIT

curl -fL --retry 3 -o "$TMP.gz" "$URL"
gunzip -c "$TMP.gz" > "$TMP"

SIZE="$(stat -c%s "$TMP")"
if [ "$SIZE" -lt 1000000 ]; then
	echo "Refusing to install DB-IP file that is only ${SIZE} bytes" >&2
	exit 1
fi

chmod 644 "$TMP"
mv -f "$TMP" "$DEST"
trap - EXIT
echo "Updated $DEST (${SIZE} bytes) from $URL"
echo "Reload httpd to pick up the new mmap: sudo systemctl reload httpd"
