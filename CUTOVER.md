# v5 hard cutover runbook

The v5 rewrite is no longer a subdirectory. `site/` in this repo *is* the new
site, and the superseded UI has been archived to `oldSites/sitev3/`. There is no
`/v5/` URL anywhere: stylesheets, `wxcharts.js`, AJAX fragments and JSON
endpoints all resolve from docroot.

Phases 1 (port the remaining pages, de-prefix URLs) and 2 (promote locally,
verify) are done. What follows is phase 3, on the server.

## What lives where after the cutover

| Location | Contents |
| --- | --- |
| `/var/www/html/` | Current code, cron scripts, FTP uploads and canonical weather data |
| `/var/www/html/cache/{v5,legacy}/` | Rebuildable, version-specific serializations and tag caches |
| `/var/www/html/generated/{v5,legacy}/` | Rebuildable camera/graph outputs (old root URLs are internally rewritten) |
| `/var/www/html/oldSites/sitev3/` | The retired UI: legacy page templates, `header.php`/`footer.php`/`leftsidebar.php` chrome, `mainstyle.css` |
| `/var/www/html/oldSites/sitev2/` etc. | Untouched |

`scripts/promote-v5.sh` holds the authoritative archive list. Files absent from
that list stayed at docroot deliberately: `basics.php`, `functions.php`,
`data.php`, `datfuncdef.php`, `climavs.php`, `mainData.php`, `unit-select.php`,
`wxdatagen.php`, `graphclim.php`, `graphclim365.php`, the `graphday*` and
`windrose` CLI generators, `HourlyLogs.php`, `monthrepgen.php` and the `cron_*`
entry points. Cron and several pages still read them.

## Server steps

1. Take a snapshot or a tarball of `/var/www/html` first — this move is not
   reversible with a single command.

2. Create the archive. The repo already holds the finished article in
   `oldSites/sitev3/` — including the frozen copies of `basics.php`,
   `functions.php`, `unit-select.php` and friends that the retired pages need —
   so upload that directory to `/var/www/html/oldSites/sitev3/` rather than
   moving files around by hand. Then delete the retired originals from docroot,
   using `ARCHIVE_FILES` in `scripts/promote-v5.sh` as the list.

   Two archived pages, `wxdatadaySimple.php` and `wxhistmonthB.php`, fatal on
   missing `data()` / `customlog()` helpers. That predates the cutover; both are
   301'd to their replacements, so nothing reaches them.

3. Sync the promoted tree from `site/` in the repo. Do **not** use
   `rsync --delete` against docroot: prod holds generated data (CSVs,
   serialised caches, `*Tags.php`, graphs, webcam stills) and the other
   `oldSites/` archives that the repo does not track.

   For the runtime-output split, dry-run and then apply:

   ```bash
   php /var/www/html/migrate_runtime_layout.php
   php /var/www/html/migrate_runtime_layout.php --go
   ```

4. Remove the now-empty `/var/www/html/v5/` if a previous deploy created one.

5. Check cron. Existing `cron_main.php`, `cron_cam.php`, and `cron_hikcam.php`
   continue to handle shared/current-site work. Add a once-per-minute crontab
   entry for `cron_legacy.php`, then run both main derivation crons by hand:

   ```bash
   php -q /var/www/html/warm_detail_summaries.php
   php -q /var/www/html/cron_main.php
   php -q /var/www/html/cron_legacy.php
   ```

6. Smoke test the public URLs and the redirect map (see below), then watch the
   Apache error log for a few minutes.

## Redirect map

`site/.htaccess` 301s every retired public URL, so inbound links and search
results keep working:

| Old URL | New URL |
| --- | --- |
| `/wx4.php` | `/RankDay.php` |
| `/wxdatadaySimple.php` | `/wxdataday.php` |
| `/wxhistmonthB.php` | `/wxhistmonth.php` |
| `/grapharchive.php`, `/graph12.php`, `/graph31.php`, `/graph_annual.php`, `/graph_daily_trend.php` | `/graphviewer.php` |
| `/windrose.php` | `/windrose_viewer.php` |
| `/wcarchive.php` | `/wx2.php` |

`/windrose.php` only redirects over HTTP; cron still calls the file directly
through the PHP CLI, so the generator is unaffected.

The same file also canonicalises `www.` and `http://` to `https://nw3weather.co.uk`.

## Rollback

Move `oldSites/sitev3/*` back to docroot and restore the previous `.htaccess`.
The new site files can coexist during the move because the archived names are
exactly those the promoted tree replaced.
