 #!/usr/bin/env bash
 # This copies the latest hikcam image to a stable filename
 set -eu

DST="/var/www/html/skycam_raw.jpg"
DST_NAME="/var/www/html/skycam_name.txt"
ITER=1000
FREQ=10

for ((n=0;n<$ITER;n++))
do
  # Most recent file could be mid-upload, so use 2nd most-recent
  IMG=$(ls -rAt /var/www/html/hikcam | tail -n2 | head -n1)
  echo "$IMG" > "$DST_NAME"
  cp "/var/www/html/hikcam/$IMG" "$DST"
  /usr/bin/php -q /var/www/html/cron_hikcam.php > /var/www/log/cronhiklog.txt
  sleep $FREQ
done
