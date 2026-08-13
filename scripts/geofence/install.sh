#!/usr/bin/env bash
# Install mod_maxminddb and enable the country geofence. Run as root:
#   sudo bash /home/ben/geofence/install.sh
set -euo pipefail

CONF_SRC="${CONF_SRC:-/home/ben/geofence/z-geofence.conf}"
CONF_DST="/etc/httpd/conf.d/z-geofence.conf"
RPM_CONF_SRC="${RPM_CONF_SRC:-/home/ben/geofence/maxminddb.conf}"
DB="/var/www/geoip/dbip-country-lite.mmdb"

if [ "$(id -u)" -ne 0 ]; then
	echo "Run as root: sudo bash $0" >&2
	exit 1
fi

if [ ! -s "$DB" ]; then
	echo "Missing GeoIP database: $DB" >&2
	echo "Run /var/www/geoip/update-db.sh as ben first." >&2
	exit 1
fi

if [ ! -f "$CONF_SRC" ]; then
	echo "Missing Apache config: $CONF_SRC" >&2
	exit 1
fi

yum install -y mod_maxminddb

install -m 644 -o root -g root "$CONF_SRC" "$CONF_DST"

# RPM snippet opens GeoLite2-City.mmdb (~54MB) even though we never query it.
# COUNTRY_DB is defined in z-geofence.conf (current DB-IP file).
if [ -f "$RPM_CONF_SRC" ]; then
	install -m 644 -o root -g root "$RPM_CONF_SRC" /etc/httpd/conf.d/maxminddb.conf
else
	cat > /etc/httpd/conf.d/maxminddb.conf <<'EOF'
<IfModule mod_maxminddb.c>
    MaxMindDBEnable On
    # CITY_DB omitted on purpose: unused, and GeoLite2-City.mmdb is 54MB.
    # COUNTRY_DB lives in z-geofence.conf.
</IfModule>
EOF
	chmod 644 /etc/httpd/conf.d/maxminddb.conf
fi

/usr/sbin/httpd -t
systemctl reload httpd
echo "Loaded modules:"
/usr/sbin/httpd -M 2>/dev/null | grep -i maxmind || true
echo "Geofence installed. Blocked countries should now receive 403."
