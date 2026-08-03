#!/usr/bin/env bash
#
# Hard cutover: archive the superseded UI and promote v5/ to docroot.
# Already applied locally; kept as the authoritative ARCHIVE_FILES list and
# server-side runbook companion to CUTOVER.md.
#
# On the server the equivalent move is:
#   mkdir -p /var/www/html/oldSites/sitev3
#   mv <archive list> /var/www/html/oldSites/sitev3/
#   mv /var/www/html/v5/* /var/www/html/
#
set -euo pipefail

cd "$(dirname "$0")/.."

SRC=site
ARCHIVE=oldSites/sitev3

# Pages, templates and stylesheets replaced by the v5 shell. Everything not
# listed here stays at docroot because cron, the v5 pages or the API need it.
ARCHIVE_FILES=(
	ajax
	BeaufortScale.php
	Bot.php
	RankDay.php
	RankMonth.php
	TablesDataMonth.php
	TagGen.php
	Top.php
	WD_LineSkipFind.php
	albgen.php
	allTagsInclude.php
	chartgen.php
	charts.php
	chead.php
	contact.php
	datLiner.php
	datamod.php
	detailDataModules.php
	errorTemplate.php
	footer.php
	ggltrack.php
	graph12.php
	graph31.php
	graph_annual.php
	graph_daily_trend.php
	graph_test.php
	grapharchive.php
	graphviewer.php
	header.php
	highreswebcam.php
	index.php
	leftsidebar.php
	mainstyle.css
	mob.php
	news.php
	phptest.php
	phptest2.php
	pondAdmin.php
	repyear.php
	sample.xhtml
	site_status.php
	skycam.php
	testBLR.php
	timelapsechive.php
	wcarchive.php
	widestyle.css
	windrose_viewer.php
	wx2.php
	wx3.php
	wx4.php
	wx5.php
	wx6.php
	wx7.php
	wx8.php
	wx9.php
	wx10.php
	wx11.php
	wx12.php
	wx13.php
	wx14.php
	wx15.php
	wx16.php
	wx_albgen.php
	wxaverages.php
	wxdataday.php
	wxdatadaySimple.php
	wxhistday.php
	wxhistmonth.php
	wxhistmonthB.php
	wxhistyear.php
	wxtempltas.php
)

mkdir -p "$ARCHIVE"

for f in "${ARCHIVE_FILES[@]}"; do
	if [ -e "$SRC/$f" ]; then
		mv "$SRC/$f" "$ARCHIVE/$f"
	else
		echo "skip (absent): $f"
	fi
done

# The archived pages keep their own frozen copies of the procedural include
# chain (basics/functions/…). Those no longer exist at docroot; do not try to
# re-copy them from SRC. Data still comes from docroot because archived
# basics.php keeps ROOT pointing there.
# (Historical one-time step during first promote — already applied.)
# for f in unit-select.php basics.php functions.php datfuncdef.php climavs.php mainData.php \
# 	graphclim365.php valcolstyle.css; do
# 	cp "$SRC/$f" "$ARCHIVE/$f"
# done

# Resolve archived includes inside the archive instead of docroot, otherwise
# both copies load and PHP fatals on redeclared classes. Data keeps coming from
# docroot via $fullpath, and ARCHIVE_WEB prefixes the archive's own links.
if [ -f "$ARCHIVE/basics.php" ]; then
	perl -0pi -e "s{\\\$fullpath = \\\$siteRoot = ROOT;}{// Archived copy: code resolves inside this directory, data still comes from docroot.\nconst ARCHIVE_WEB = '/oldSites/sitev3';\n\\\$fullpath = ROOT;\n\\\$siteRoot = __DIR__ . '/';}" "$ARCHIVE/basics.php"
fi
perl -pi -e "s{^\\\$siteRoot = '/var/www/html/';}{\\\$siteRoot = dirname(__DIR__) . '/';}" "$ARCHIVE"/ajax/*.php 2>/dev/null || true
perl -pi -e "s{require_once ROOT\\.'mainData\\.php';}{require_once \\\$siteRoot.'mainData.php';}" "$ARCHIVE"/ajax/*.php 2>/dev/null || true
# Promote the v5 tree, including dotfiles, then drop the empty directory.
shopt -s dotglob
mv "$SRC"/v5/* "$SRC"/
shopt -u dotglob
rmdir "$SRC/v5"

echo "Promotion complete."
echo "Note: albgen.php / wx_albgen.php in the archive also gained a fallback from"
echo "photos/albums/ to albums/, applied by hand rather than by this script."
